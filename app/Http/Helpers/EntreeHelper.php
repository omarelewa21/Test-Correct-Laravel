<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\Exceptions\CleanRedirectException;
use tcCore\Exceptions\RedirectAndExitException;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\SamlMessage;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\TemporaryLogin;
use tcCore\TestParticipant;
use tcCore\User;

class EntreeHelper
{
    const ENTREESTUDENTREDIRECT = 'https://www.test-correct.nl/welcome-student';

    private $attr;

    private $messageId;

    private $location = null;

    private $school = null;

    public $laravelUser = null;

    public $shouldThrowAnErrorDuringTransaction = false;

    private $brinFourErrorDetected = false;

    protected $rolesToTransformToTeacher = ['employee'];

    protected $context = null;

    protected $messageUuid = null;

    private $location_based_on_brin_six = false;

    protected $origAttr;

    protected $emailMaybeEmpty = false;

    protected $entreeReason;
    protected $finalRedirectTo;
    protected $mId;

    public function __construct($attr, $messageId)
    {
        $this->attr = $this->transformAttributesIfNeededAndReturn($attr);
        $this->messageId = $messageId;

        $this->retrieveDataFromSession();
    }

    protected function logger($data)
    {
        //logger($data);
    }

    private function retrieveDataFromSession()
    {
        $this->entreeReason = session()->get('entreeReason');
        $this->finalRedirectTo = session()->get('finalRedirectTo');
        $this->mId = session()->get('mId');
    }

    public static function initAndHandleFromRegisterWithEntreeAndTUser(User $user, $attr)
    {

        $instance = new self($attr, '');
        $instance->setContext('livewire');
        $instance->laravelUser = $user;
        $instance->emailMaybeEmpty = optional($user->location)->lvs_active_no_mail_allowed;
        $instance->handleScenario1(['afterLoginMessage' => __("onboarding.Welkom bij Test-Correct, je kunt nu aan de slag."), 'internal_page' => '/users/welcome']);
        return true;
    }

    protected function isRegistering()
    {
        return ($this->entreeReason === 'register');
    }

    public function handleIfRegistering()
    {
        if (!$this->isRegistering()) {
            return false;
        }
        $this->setLocationWithSamlAttributes();
        if (config('entree.use_with_2_urls') && $url = $this->redirectIfSmallSetAndSsoAvailable(true)) {
            return $url;
        }

        $data = [
            'emailAddress'       => $this->getEmailFromAttributes(),
            'role'               => $this->getRoleFromAttributes(),
            'encryptedEckId'     => Crypt::encryptString($this->getEckIdFromAttributes()),
            'brin'               => $this->getBrinFromAttributes(),
            'location'           => $this->location,
            'school'             => $this->school,
            'brin4ErrorDetected' => $this->brinFourErrorDetected,
            'lastName'           => $this->getLastNameFromAttributes(),
            'nameSuffix'         => $this->getSuffixFromAttributes(),
            'firstName'          => $this->getFirstNameFromAttributes(),
        ];

        $data = (object)$data;

        if ($url = $this->handleIfRegisteringAndNotATeacher($data)) {
            return $url;
        }

        if ($url = $this->handleIfRegisteringAndNoEckId($data)) {
            return $url;
        }

        if ($url = $this->handleIfRegisteringAndNoBrincode($data)) {
            return $url;
        }

        if ($data->user = $this->handleIfRegisteringAndUserBasedOnEckId($data)) {
            if (!$data->user instanceof User and is_string($data->user)) {
                return $data->user;
            }
        }

        if ($url = $this->handleIfNonExistingEckIdButExistingEmail($data)) {
            return $url;
        }

        $samlMessage = $this->createSamlMessageFromRegisterData($data);
        return $this->redirectToUrlAndExit(route('onboarding.welcome.entree', ['samlId' => $samlMessage->uuid]));
    }

    protected function getOnboardingUrlWithOptionalMessage($message = null, $entree = false, $params = [])
    {
        $route = 'onboarding.welcome';
        if ($entree) {
            $route = 'onboarding.welcome.entree';
        }
        $queryAr = [];
        if ($message) {
            $queryAr['entree_message'] = $message;
        }
        if (count($params)) {
            $queryAr = array_merge($params, $queryAr);
        }
        return route($route, $queryAr);
    }

    protected function handleIfRegisteringAndUserBasedOnEckId($data)
    {
        if ($user = User::filterByEckid(Crypt::decryptString($data->encryptedEckId))->first()) {

            if (!$user->isA('teacher')) {
                return $this->redirectToUrlAndExit(self::ENTREESTUDENTREDIRECT);
            }

            if (!$user->hasImportMailAddress()) { // regular user
                if ($data->emailAddress) {
                    if ($user2 = User::where('username', $data->emailAddress)->first()) {
                        if ($user->getKey() != $user2->getKey()) {
                            return $this->redirectToUrlAndExit($this->getOnboardingUrlWithOptionalMessage(__('onboarding-welcome.Je entree account kan niet gebruikt worden om een account aan te maken in Test-Correct. Neem contact op met support.')));
                        }
                    }
                }

                $this->laravelUser = $user;
                if ($this->location) {
                    if ($user->isAllowedToSwitchToSchoolLocation($this->location)) {
                        // account already correct
                        Auth::login($user);
                        $url = $this->laravelUser->getRedirectUrlSplashOrStartAndLoginIfNeeded(['afterLoginMessage' => __('onboarding-welcome.Je bestaande Test-Correct account is al gekoppeld aan je Entree account. Je kunt vanaf nu ook inloggen met Entree.'), 'internal_page' => '/users/welcome']);
                        return $this->redirectToUrlAndExit($url);
                    }
                    // if in same school, add school location
                    $schoolFromSchoolLocation = $this->location->school;
                    if ($schoolFromSchoolLocation) {
                        if ($url = $this->handleIfRegisteringAndSchoolIsAllowed($user, $schoolFromSchoolLocation)) {
                            return $url;
                        }
                    }
                } else if ($this->school){
                    // registering can't take place as there is no location, we need to get the registration form in play
                    return $user;
//                    if($url = $this->handleIfRegisteringAndSchoolIsAllowed($user,$this->school)){
//                        return $url;
//                    }
                }
                // if not contact support
                $url = BaseHelper::getLoginUrlWithOptionalMessage(__('onboarding-welcome.Je bestaande Test-Correct account kan niet geupdate worden. Neem contact op met support.'), true);
                return $this->redirectToUrlAndExit($url);
            }

            if (!$data->emailAddress || substr_count($data->emailAddress, '@') < 1) {
                return $this->redirectToUrlAndExit($this->getOnboardingUrlWithOptionalMessage(__('onboarding-welcome.Je entree account kan niet gebruikt worden om een account aan te maken in Test-Correct. Neem contact op met support.')));
            }

            if (!$this->location && $this->school) {
                if (!$user->isAllowedSchool($this->school)) {
                    $url = BaseHelper::getLoginUrlWithOptionalMessage(__('onboarding-welcome.Je bestaande Test-Correct account kan niet geupdate worden. Neem contact op met support.'), true);
                    return $this->redirectToUrlAndExit($url);
                }
            }

            // import user
            return $user;
        }
        return null;
    }


    protected function handleIfRegisteringAndSchoolIsAllowed(User $user, School $school)
    {
        if ($user->isAllowedSchool($school)) {
            $user->school_location_id = $this->location->getKey();
            $user->save();
            $user->addSchoolLocationAndCreateDemoEnvironment($this->location);
            $url = $this->laravelUser->getRedirectUrlSplashOrStartAndLoginIfNeeded(['afterLoginMessage' => __('onboarding-welcome.Je bestaande Test-Correct account is geupdate met de schoollocaties die we vanuit Entree hebben meegekregen. We hebben je in de schoollocatie gezet. Je kunt vanaf nu ook inloggen met Entree.', ['name' => $this->location->name]), 'internal_page' => '/users/welcome']);
            return $this->redirectToUrlAndExit($url);
        }
        return false;
    }

    protected function handleIfRegisteringAndNoBrincode($data)
    {
        $brinCode = $data->brin;
        $exit = true;

        if ($this->setLocationBasedOnBrinSixIfTheCase($brinCode)) {
            if ($this->location) {
                $exit = false;
            }
        } else if (strlen($brinCode) === 4) {
            if (School::where('external_main_code', $brinCode)->count() === 1) {
                $exit = false;
            }
        }

        // no brincode found
        if ($exit) {
            return $this->redirectIfUnknownBrinForRegistration();
        }
        return false;
    }

    protected function redirectIfUnknownBrinForRegistration()
    {
        return $this->redirectToUrlAndExit($this->getOnboardingUrlWithOptionalMessage(__('onboarding-welcome.Je school is helaas nog niet bekend in Test-Correct. Vul dit formulier in om een account aan te maken')));
    }

    protected function handleIfRegisteringAndNoEckId($data)
    {
        $eckId = Crypt::decryptString($data->encryptedEckId);
        if (!$eckId || strlen($eckId) < 5) {
            $samlMessage = $this->createSamlMessageFromRegisterData($data);
            return $this->redirectToUrlAndExit($this->getOnboardingUrlWithOptionalMessage(__('onboarding-welcome.Je kunt geen Test-Correct account aanmaken via Entree. Vul dit formulier in om een account aan te maken'), false, ['registerId' => $samlMessage->uuid]));
        }
        return false;
    }

    protected function handleIfNonExistingEckIdButExistingEmail($data)
    {
        $eckId = Crypt::decryptString($data->encryptedEckId);

        if (!$user = User::findByEckId($eckId)->first()) {
            if ($data->emailAddress) {
                if ($user = User::where('username', $data->emailAddress)->first()) {
                    return $this->redirectToUrlAndExit($this->getOnboardingUrlWithOptionalMessage(__('onboarding-welcome.Je entree account kan niet gebruikt worden om een account aan te maken in Test-Correct. Neem contact op met support.')));
                }
            }
        }

        return false;
    }

    protected function handleIfRegisteringAndNotATeacher($data)
    {
        if ($data->role !== 'teacher') {
            return $this->redirectToUrlAndExit(self::ENTREESTUDENTREDIRECT);
        }
        return false;
    }

    public static function initWithMessage(SamlMessage $message)
    {
        $instance = new self([], '');
        $eckId = Crypt::decryptString($message->eck_id);
        $instance->laravelUser = User::findByEckId($eckId)->first();
        $instance->location = $instance->laravelUser->schoolLocation;
        $instance->messageUuid = $message->uuid;

        $instance->attr['eckId'] = [$eckId];

        return $instance;
    }

    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    protected function transformAttributesIfNeededAndReturn($attr)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $loggerAttr = $attr;
        $loggerAttr['eckId'] = '';
        if (!empty($attr['eckId'][0]) && is_string($attr['eckId'][0])) {
            $loggerAttr['eckId'] = substr((string)$attr['eckId'][0], -5);
        }
        $this->logger(json_encode($loggerAttr));

        // we may get employee, then we transfer it to teacher
        if (array_key_exists('eduPersonAffiliation', $attr) && in_array(strtolower($attr['eduPersonAffiliation'][0]),
                $this->rolesToTransformToTeacher)) {
            $attr['eduPersonAffiliation'][0] = 'teacher';
        }

        return $attr;
    }

    public function tryAccountMatchingWhenNoMailAttributePresent(User $oldUserWhereWeWouldLikeToMergeTheImportAccountTo)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }

        if (null == $this->laravelUser) {
            $this->addLogRows('tryAccountMatchingWhenNoMailAttributePresent');
            $url = route('auth.login', ['tab' => 'fatalError', 'fatal_error_message' => 'auth.roles_do_not_match_up', 'block_back' => true,]);
            return $this->redirectToUrlAndExit($url);
        }

        return $this->mergeAccountStrategies($oldUserWhereWeWouldLikeToMergeTheImportAccountTo);

    }

    public function redirectIfBrinUnknown()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->setLocationWithSamlAttributes();
        if ($this->location == null) {
            $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.brin_not_found']);
            // dit lijkt fout als Brin6 detecteerd als null dan wordt brinFourErrorDetected of true gezet;
            if ($this->brinFourErrorDetected) {
                $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.brin_four_detected']);
            }
            return $this->redirectToUrlAndExit($url);
        }
        return $this->location;
    }

    private function setLocationBasedOnBrinSixIfTheCase($brinZesCode)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $external_main_code = substr($brinZesCode, 0, 4);
        if (strlen($brinZesCode) === 6) {
            $external_sub_code = substr($brinZesCode, 4, 2);
            $this->location = SchoolLocation::where('external_main_code', $external_main_code)
                ->where('external_sub_code', $external_sub_code)
                ->first();
            $this->location_based_on_brin_six = true;
            // move location if we have a master school en sub locations from the uwlr import
            if($this->location->import_merge_school_location_id){
                $this->location = SchoolLocation::find($this->location->import_merge_school_location_id);
            }
            return true;
        }
        return false;
    }

    protected function getSchoolLocationsBasedOnSchoolIdAndActiveEntreeSSO($schoolId)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        return SchoolLocation::where('school_id', $schoolId)
            ->where('sso_type', SchoolLocation::SSO_ENTREE)
            ->where('sso_active', 1)
            ->get();
    }

    private function setLocationWithSamlAttributes()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (null !== $this->location) {
            // we did run this method before
            return true;
        }

        $brinZesCode = $this->getBrinFromAttributes();
        if (null === $brinZesCode) {
            $this->brinFourErrorDetected = true;
            return false;
        }

        if ($this->setLocationBasedOnBrinSixIfTheCase($brinZesCode)) {
            return true;
        }

        $external_main_code = substr($brinZesCode, 0, 4);

        if (strlen($brinZesCode) === 4) {
            // 1. zoeken binnen de scholengemeenschap (is school)
            // 2. schoollocaties zoeken binnen deze scholengemeenschap die voldoen omdat ze ook een entree koppeling hebben
            // 3. is er een locatie die ook past bij de gebruiker
            // 4. zo ja bij voorkeur die pakken die nu ook al actief is en anders de eerste die wel voldoet
            // 5. indien geen gevonden dan brinFourErrorDetected
            $school = School::where('external_main_code', $external_main_code)->get();
            if ($school->count() === 1) {
                $this->school = $school->first();
                $locations = $this->getSchoolLocationsBasedOnSchoolIdAndActiveEntreeSSO($this->school->getKey());
                if ($locations->count() > 0) {
                    if ($this->isTeacherBasedOnAttributes()) {
                        // teacher (later on there will be a match on role)
                        $allowedLocations = $locations->filter(function (SchoolLocation $sl) {
                            return User::findByEckidAndSchoolLocationIdForTeacher($this->getEckIdFromAttributes(),
                                    $sl->getKey())->first() != null;
                        });
                        if ($allowedLocations->count() > 0) {
                            // the locations for which the teacher is allowed to access
                            if ($allowedLocations->count() === 1) {
                                $this->location = $allowedLocations->first();
                                return true;
                            } else {
                                // search the school location where the teacher was logged in last if available
                                $lastLocation = $allowedLocations->first(function (SchoolLocation $sl) {
                                    return User::findByEckidAndSchoolLocationIdForTeacher($this->getEckIdFromAttributes(),
                                            $sl->getKey())
                                            ->where('users.school_location_id', $sl->getKey())->first() != null;
                                });
                                if ($lastLocation->count() === 1) {
                                    // there was a location for which the teacher was logged in last
                                    $this->location = $lastLocation->first();
                                    return true;
                                } else {
                                    // sorry not available so we just take the first we can find
                                    $this->location = $allowedLocations->first();
                                    // set user schoollocation for later checks;
                                    $u = User::filterByEckid($this->getEckIdFromAttributes())->first();
                                    $u->school_location_id = $this->location->getKey();
                                    $u->save();
                                    if (null != $this->laravelUser) {
                                        $this->laravelUser->school_location_id = $this->location->getKey();
                                    }

                                    return true;
                                }
                            }
                        }
                    } else {
                        // student (later on there will be a match on role)
                        $allowedLocations = $locations->filter(function (SchoolLocation $sl) {
                            return User::findByEckidAndSchoolLocationIdForUser($this->getEckIdFromAttributes(),
                                    $sl->getKey())->first() != null;
                        });
                        if ($allowedLocations->count() > 0) {
                            // the locations for which the user is allowed to access
                            if ($allowedLocations->count() === 1) {
                                $this->location = $allowedLocations->first();
                                return true;
                            } else {
                                // this should never happen
                                $this->location = $allowedLocations->first();
                                return true;
                            }
                        }
                    }
                }
            }

//            // uitzoeken 1 locatie aanwezig dan setten
//            $locations = SchoolLocation::where('external_main_code', $external_main_code)->get();
//            if ($locations->count() === 1) {
//                $this->location = $locations->first();
//                return true;
//            }
//            // indien meerdere met 4 code dan gebruiker zoeken en locatie daarbij zoeken
////            User::findByEckId($this->getEckIdFromAttributes())

            $this->brinFourErrorDetected = true;
        }
    }

    public static function shouldPromptForEntree(User $user)
    {
        if ($user->isToetsenbakker()) {
            return false;
        }
        if ($user->isTestCorrectUser()) {
            return false;
        }

        if($user->isValidExamCoordinator()){
            return false;
        }
        return (optional($user->schoolLocation)->lvs_active && empty($user->eck_id));
    }

    private function getEckIdFromAttributes()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (isset($this->attr['eckId']) && isset($this->attr['eckId'][0])) {
            if (is_array($this->attr['eckId'][0]) && empty($this->attr['eckId'][0])){
                return '';
            }
            return $this->attr['eckId'][0];
        }
        return null;
    }

    private function getBrinFromAttributes()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (array_key_exists('nlEduPersonHomeOrganizationBranchId',
                $this->attr) && $this->attr['nlEduPersonHomeOrganizationBranchId'][0]) {
            return $this->attr['nlEduPersonHomeOrganizationBranchId'][0];
        }
        if (array_key_exists('nlEduPersonHomeOrganizationId',
                $this->attr) && $this->attr['nlEduPersonHomeOrganizationId'][0]) {
            return $this->attr['nlEduPersonHomeOrganizationId'][0];
        }

        return null;
    }

    private function getEmailFromAttributes()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (array_key_exists('mail', $this->attr)
            && $this->attr['mail'][0]
            && Str::contains($this->attr['mail'][0], '@')
            && $this->attr['mail'][0] !== 'fakeemail@test-correct.nl') {
            return $this->attr['mail'][0];
        }
        return null;
    }

    private function hasEmailAttribute()
    {
        return !!$this->getEmailFromAttributes();
    }

    private function getFirstNameFromAttributes()
    {
        if (array_key_exists('givenName',
                $this->attr) && $this->attr['givenName'][0]) {
            return $this->attr['givenName'][0];
        }
        return null;
    }

    private function getLastNameFromAttributes()
    {
        if (array_key_exists('sn',
                $this->attr) && $this->attr['sn'][0]) {
            return $this->attr['sn'][0];
        }
        return null;
    }

    private function getSuffixFromAttributes()
    {
        if (array_key_exists('nlEduPersonTussenvoegsels',
                $this->attr) && $this->attr['nlEduPersonTussenvoegsels'][0]) {
            return $this->attr['nlEduPersonTussenvoegsels'][0];
        }
        return null;
    }

    private function getRoleFromAttributes()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (array_key_exists('eduPersonAffiliation',
                $this->attr) && $this->attr['eduPersonAffiliation'][0]) {
            return $this->attr['eduPersonAffiliation'][0];
        }
        return null;
    }

    private function createSamlMessageFromRegisterData($data)
    {
        $data->schoolId = ($data->school) ? $data->school->getKey() : null;
        $data->locationId = ($data->location) ? $data->location->getKey() : null;
        $data->school = null;
        $data->location = null;
        $data->userId = (property_exists($data, 'user') && $data->user) ? $data->user->getKey() : null;
        $data->user = null;
        return SamlMessage::create([
            'data'       => $data,
            'eck_id'     => 'not needed',
            'message_id' => 'not needed',
        ]);
    }

    private function createSamlMessage()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();

        return SamlMessage::create([
            'message_id' => $this->messageId,
            'eck_id'     => Crypt::encryptString($this->getEckIdFromAttributes()),
            'email'      => $this->getEmailFromAttributes(),
        ]);
    }

    private function createSamlMessageWithEmptyEmail()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();

        return SamlMessage::create([
            'message_id' => $this->messageId,
            'eck_id'     => Crypt::encryptString($this->getEckIdFromAttributes()),
            'email'      => '',
        ]);
    }

    private function validateAttributes()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (!array_key_exists('eckId', $this->attr) || !array_key_exists(0, $this->attr['eckId'])) {
            logger('No eckId found');
            logger('==== credentials ====');
            logger($this->attr);
            logger('=======');
            throw new \Exception('no eckId found in saml request');
        }

        if (!$this->emailMaybeEmpty && !$this->getEmailFromAttributes()) {
            logger('No mail found');
            logger('==== credentials ====');
            $attr = $this->attr;
            $attr['eckId last chars'] = substr($attr['eckId'][0], -5);
            unset($attr['eckId']);
            logger($attr);
            logger('=======');

            optional($this->location)->sendSamlNoMailAddresInRequestDetectedMailIfAppropriate($attr);

//            throw new \Exception('no mail found in saml request');
        }
    }

    public function blockIfReplayAttackDetected()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $message = SamlMessage::whereMessageId($this->messageId)->first();
        if ($message) {
            dd('preventing reuse of messageId');
        }
    }

    protected function hasLVS()
    {
        return (null != $this->location && !empty($this->location->lvs_type));
    }

    public function redirectIfscenario5()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if ($this->hasLVS()) {
            return true;
        }
//        if ($this->location->lvs_active) {
//            return true;
//        }
        $this->handleScenario5();
    }

    public function handleScenario5()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();
        if ($url = $this->redirectIfBrinNotSso()) {
            return $url;
        }

        if ($this->isTeacherBasedOnAttributes()) {
            $this->laravelUser = User::findByEckidAndSchoolLocationIdForTeacher($this->getEckIdFromAttributes(),
                $this->location->getKey())->first();
        } else {
            $this->laravelUser = User::findByEckidAndSchoolLocationIdForUser($this->getEckIdFromAttributes(),
                $this->location->getKey())->first();
        }

//        $this->laravelUser = User::findByEckId($this->attr['eckId'][0])->first();
        if ($this->laravelUser) {
            // return true is hier waarschijnlijk voldoende omdat je dan via scenario 1 wordt ingelogged;
            $this->handleUpdateUserWithSamlAttributes();

            return $this->handleEndRedirect();

        }

        // if user is in the system with another school location, then we need to redirect and show the error that there is no option to merge
        if (User::findByEckId($this->getEckIdFromAttributes())->exists()) {
            $url = route('auth.login',
                ['tab' => 'login', 'entree_error_message' => 'auth.user_not_in_same_school_please_contact_helpdesk']);;
            return $this->redirectToUrlAndExit($url);
        }
        // redirect to maak koppelingscherm;

        $message = $this->createSamlMessage();
        $url = route('auth.login', ['tab' => 'entree', 'uuid' => $message->uuid]);
        return $this->redirectToUrlAndExit($url);
    }

    protected function handleEndRedirect($options = [])
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        // make sure the standard procedure is first handled before possible final redirect.
        $url = $this->laravelUser->getRedirectUrlSplashOrStartAndLoginIfNeeded($options);

        // check if there is a data collection which needds to be checked
        if ($this->finalRedirectTo) {
            $url = $this->finalRedirectTo;
        }

        return $this->redirectToUrlAndExit($url);
    }

    protected function isTeacherBasedOnAttributes()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        return strtolower($this->getRoleFromAttributes()) == 'teacher';
    }

    public function redirectIfSmallSetAndSsoAvailable($register = false)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->setLocationWithSamlAttributes();
        $this->hasActiveEntreeSSOBasedOnSchool = false;
        if ($this->school) {
            $this->hasActiveEntreeSSOBasedOnSchool = !!($this->getSchoolLocationsBasedOnSchoolIdAndActiveEntreeSSO($this->school->getKey())->count());
        }
        if (request()->get('set') !== 'full'
            && (
                (optional($this->location)->sso_active == 1 && optional($this->location)->sso_type === SchoolLocation::SSO_ENTREE)
                || $this->hasActiveEntreeSSOBasedOnSchool
            )
        ) {
            // we probably have a small set so go for the big set
            // we need an url to go to samle login with setting for the big set
            $url = route('saml2_login', ['idpName' => 'entree', 'set' => 'full', 'entreeRegister' => $register, 'mId' => $this->mId]);
            sleep(2);
            return $this->redirectToUrlAndExit($url);
        }
    }

    public function redirectIfBrinNotSso()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->setLocationWithSamlAttributes();
        if (optional($this->location)->sso_active != 1) {
            $url = route('auth.login',
                ['tab' => 'login', 'entree_error_message' => 'auth.school_not_registered_for_sso']);
            return $this->redirectToUrlAndExit($url);
        }
    }

    public function redirectIfUserWasNotFoundForEckIdAndActiveLVS()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();
        $this->setLocationWithSamlAttributes();
        $this->setLaravelUser();

        if ($this->laravelUser) {
            return true;
        }
        if (null != $this->location && empty($this->location->lvs_type)) {
            return true;
        }

        $url = route('auth.login',
            ['tab' => 'login', 'entree_error_message' => 'auth.school_info_not_synced_with_test_correct']);
        return $this->redirectToUrlAndExit($url);
    }

    public function redirectIfNoUserWasFoundForEckId()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();
        $this->setLocationWithSamlAttributes();
        $this->setLaravelUser();

        if ($this->laravelUser) {
            return true;
        }

        $url = route('auth.login',
            ['tab' => 'login', 'entree_error_message' => 'auth.school_info_not_synced_with_test_correct']);
        return $this->redirectToUrlAndExit($url);
    }

    public function redirectIfUserNotInSameSchool()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();
        if (null == $this->location) {
            $this->setLocationWithSamlAttributes();
        }
        if (null != $this->location) {
            if (null == $this->laravelUser) {
                $this->setLaravelUser();
            }
            if (null != $this->laravelUser) {
                if ($this->isTeacherBasedOnAttributes()) {
                    if ($this->laravelUser->allowedSchoolLocations->contains($this->location->getKey())) {
                        $this->laravelUser->school_location_id = $this->location->getKey();
                        $this->laravelUser->save();
                        return true;
                    }
                } else {
                    if ($this->location && $this->location->is($this->laravelUser->schoolLocation)) {
                        return true;
                    }
                }
            }
        }

        $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.user_not_in_same_school']);

        return $this->redirectToUrlAndExit($url);
    }

    public function redirectIfUserNotHasSameRole()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();

        if (null == $this->location) {
            $this->setLocationWithSamlAttributes();
        }

        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }

        if (null == $this->laravelUser) {
            return true;//$this->redirectIfNoUserWasFoundForEckId();
        }

        if (optional($this->laravelUser)->isA($this->getRoleFromAttributes())) {
            return true;
        }

        $this->addLogRows('redirectIfUserNotHasSameRole');
        $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.roles_do_not_match_up']);
        return $this->redirectToUrlAndExit($url);
    }

    protected function addLogRows($functionName)
    {
//        logger($functionName);
//        logger('id of laravel user ' . optional($this->laravelUser)->getKey());
        $this->attr['eckId'][0] = substr($this->attr['eckId'][0], -10);
//        logger($this->attr);
    }

    public function handleScenario1($options = null)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();

        if (null == $this->location) {
            $this->setLocationWithSamlAttributes();
        }

        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }

        if (null == $this->laravelUser) {
            $this->addLogRows('handleScenario1');
            $url = route('auth.login', ['tab' => 'fatalError', 'fatal_error_message' => 'auth.roles_do_not_match_up', 'block_back' => true]);
            return $this->redirectToUrlAndExit($url);
        }

        $this->handleUpdateUserWithSamlAttributes();

        return $this->handleEndRedirect($options);
    }

    public function handleScenario2IfAddressIsKnownInOtherAccount()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->validateAttributes();

        if (null == $this->location) {
            $this->setLocationWithSamlAttributes();
        }

        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }

        if (null == $this->laravelUser) {
            $this->addLogRows('handleScenario2IfAddressIsKnownInOtherAccount');
            $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.roles_do_not_match_up']);
            return $this->redirectToUrlAndExit($url);
        }

        $otherUserWithEmailAddress = User::where('username', $this->getEmailFromAttributes())
            ->whereNotNull('username') // in case of no mail address from entree
            ->where('id', '<>', $this->laravelUser->id)
            ->first();
        if ($otherUserWithEmailAddress) {
            return $this->mergeAccountStrategies($otherUserWithEmailAddress);
        }

        return false;
    }

    private function redirectIfRolesDontMatch(User $userOne, User $userTwo)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $rolePass = false;

        if ($userOne->isA('teacher') && $userTwo->isA('teacher')) {
            $rolePass = true;
        }

        if ($userOne->isA('student') && $userTwo->isA('student')) {
            $rolePass = true;
        }

        if ($rolePass === false) {
            return $this->redirectToUrlAndExit(
                route('auth.login', [
                    'tab' => 'fatalError', 'fatal_error_message' => 'auth.roles_do_not_match_up', 'block_back' => true,
                ])
            );
        }
        return true;
    }

    private function handleMatchingWithinSchoolLocation(User $oldUser, User $user)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $result = $this->redirectIfRolesDontMatch($oldUser, $user);
        if ($result !== true) {
            return $result;
        }

        try {
            DB::beginTransaction();
            $this->copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser($oldUser, $user);
            if ($this->shouldThrowAnErrorDuringTransaction) {
                throw new \Exception('Simmulating error during matching procedure');
            }
            DB::commit();
        } catch (\Exception $e) {
            logger('@@@@@ rollback of transformation');
            logger($e->getMessage());
            DB::rollback();
            $url = route('auth.login',
                ['tab' => 'login', 'entree_error_message' => 'auth.error_while_syncing_please_contact_helpdesk']);
            return $this->redirectToUrlAndExit($url);
        }
        return true;
    }


    public function copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser(User $oldUser, User $user)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        // move test participant to old user
        TestParticipant::where('user_id', $user->getKey())->update(['user_id' => $oldUser->getKey()]);

        $eckId = $user->eckId;
        $user->removeEckId();
        if (!is_null($user->user_table_external_id) && !empty($user->user_table_external_id)) {
            if ($user->isA('teacher')) {
                $oldUser->updateExternalIdWithSchoolLocation($user->user_table_external_id, $user->school_location_id);
            }
            $oldUser->external_id = $user->user_table_external_id;
            $user->removeExternalId();
            $user->save();
        }
        $oldUser->setEckidAttribute($eckId);
        $oldUser->transferClassesFromUser($user);
        foreach (['name', 'name_first'] as $key) {
            $oldUser->$key = $user->$key;
        }
        $oldUser->save();
        $this->laravelUser = $oldUser;
        $user->delete();
    }

    private function handleMatchingTeachersInKoepel(User $oldUser, User $user)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $result = $this->redirectIfRolesDontMatch($oldUser, $user);
        if ($result !== true) {
            return $result;
        }

        if ($oldUser->isA('teacher')) {
            try {
                DB::beginTransaction();
                $oldUser->addSchoolLocation($user->schoolLocation);
                $this->copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser($oldUser, $user);
                if ($this->shouldThrowAnErrorDuringTransaction) {
                    throw new \Exception('Simmulating error during matching procedure');
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                $url = route('auth.login',
                    ['tab' => 'login', 'entree_error_message' => 'auth.error_while_syncing_please_contact_helpdesk']);
                return $this->redirectToUrlAndExit($url);
            }
            return true;
        }
    }

    private function handleUpdateUserWithSamlAttributes(): void
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $emailFromEntree = false;
        if ($this->getEmailFromAttributes()) {
            $emailFromEntree = $this->getEmailFromAttributes();
        }

        if ($emailFromEntree) {
            $this->laravelUser->username = $emailFromEntree;
        }
        $this->laravelUser->save();

        // als er geen stamnummer(external_id) voor de student beschikbaar is haal het stamnummer uit het emailadres
        // dat wordt aangeleverd via Entree stamnummer is dan alles wat voor de @ staat;
        if ($this->laravelUser->schoolLocation->is_rtti_school_location == 1 && $emailFromEntree && $this->laravelUser->isA('student') && empty($this->laravelUser->externalId)) {

            $parts = explode('@', $emailFromEntree);

            if (is_array($parts) && array_key_exists(0, $parts) && $parts[0]) {
                $this->laravelUser->external_id = $parts[0];
            }
        }

        $this->laravelUser->save();
    }

    private function redirectToUrlAndExit($url)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->logger('url ' . $url);
        if (App::runningUnitTests()) {
            return $url;
        }
        if ($this->context === 'livewire') {
            return redirect()->to($url);
        }

        throw new CleanRedirectException($url);
    }

    public function setLaravelUser(): void
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));

        if (null == $this->laravelUser) {
            if (strtolower($this->getRoleFromAttributes()) == 'teacher') {
                $this->laravelUser = User::findByEckidAndSchoolLocationIdForTeacher(
                    $this->getEckIdFromAttributes(),
                    $this->location->getKey()
                )->first();
            } else {
                $this->laravelUser = User::findByEckidAndSchoolLocationIdForUser(
                    $this->getEckIdFromAttributes(),
                    $this->location->getKey()
                )->first();
            }

            if (null == $this->laravelUser) {
                // could be that they have the wrong role, so check the other way around (somewhere else there's the role check);
                if (strtolower($this->getRoleFromAttributes()) != 'teacher') {
                    $this->laravelUser = User::findByEckidAndSchoolLocationIdForTeacher(
                        $this->getEckIdFromAttributes(),
                        $this->location->getKey()
                    )->first();
                } else {
                    $this->laravelUser = User::findByEckidAndSchoolLocationIdForUser(
                        $this->getEckIdFromAttributes(),
                        $this->location->getKey()
                    )->first();
                }
            }
        }

        $this->logger('laravel user id ' . optional($this->laravelUser)->getKey());
    }

    public function blockIfEckIdAttributeIsNotPresent()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if (!array_key_exists('eckId', $this->attr) || !array_key_exists(0, $this->attr['eckId'])) {
            $url = route('auth.login',
                [
                    'tab'                  => 'login',
                    'entree_error_message' => 'auth.no_eck_id_attribute_found_in_saml_request_school_location_does_not_support_login_without_email'
                ]
            );
            return $this->redirectToUrlAndExit($url);
        }
    }

    public function blockIfSchoolLvsActiveNoMailNotAllowedWhenMailAttributeIsNotPresent()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $this->emailMaybeEmpty = optional($this->location)->lvs_active_no_mail_allowed;
        $this->validateAttributes();

        if (!$this->emailMaybeEmpty && !($this->getEmailFromAttributes())) {
            $url = route('auth.login',
                [
                    'tab'                  => 'login',
                    'entree_error_message' => 'auth.no_mail_attribute_found_in_saml_request_school_location_does_not_support_login_without_email'
                ]
            );
            return $this->redirectToUrlAndExit($url);
        }
    }

    public function redirectIfNoMailPresentScenario()
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        $userFromSamlRequest = User::findByEckId($this->getEckIdFromAttributes())->first();
        if ($this->emailMaybeEmpty && !($this->getEmailFromAttributes()) && optional($userFromSamlRequest)->hasImportMailAddress()) {
            $samlMessage = $this->createSamlMessageWithEmptyEmail();

            $url = route('auth.login', [
                    'tab'  => 'no_mail_present',
                    'uuid' => $samlMessage->uuid
                ]
            );
            return $this->redirectToUrlAndExit($url);
        }
    }

    public static function handleNewEmailForUserWithoutEmailAttribute($message, string $username)
    {
        if ($user = User::findByEckId(Crypt::decryptString($message->eck_id))->first()) {
            $user->username = $username;
            $user->save();
        }

        return $user;
    }

    private function mergeAccountStrategies(User $userWhereWeWouldLikeToMergeTheImportAccountTo)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)', __FILE__, __METHOD__, __LINE__));
        if ($this->laravelUser->isA('Student')) {
            if (!$this->laravelUser->inSchoolLocationAsUser($userWhereWeWouldLikeToMergeTheImportAccountTo)) {
                $url = route('auth.login', [
                    'tab'                 => 'fatalError',
                    'fatal_error_message' => 'auth.student_account_not_found_in_this_location',
                    'block_back'          => true
                ]);
                return $this->redirectToUrlAndExit($url);
            } else {
                return $this->handleMatchingWithinSchoolLocation($userWhereWeWouldLikeToMergeTheImportAccountTo,
                    $this->laravelUser);
            }
        } elseif ($this->laravelUser->isA('Teacher')) {
            ActingAsHelper::getInstance()->setUser($userWhereWeWouldLikeToMergeTheImportAccountTo);
            if ($this->laravelUser->inSchoolLocationAsUser($userWhereWeWouldLikeToMergeTheImportAccountTo)) {
                DemoHelper::moveSchoolLocationDemoClassToCurrentYearIfNeeded($userWhereWeWouldLikeToMergeTheImportAccountTo->schoolLocation);
                return $this->handleMatchingWithinSchoolLocation($userWhereWeWouldLikeToMergeTheImportAccountTo,
                    $this->laravelUser);
            } elseif ($this->laravelUser->inSameKoepelAsUser($userWhereWeWouldLikeToMergeTheImportAccountTo)) {
                DemoHelper::moveSchoolLocationDemoClassToCurrentYearIfNeeded($userWhereWeWouldLikeToMergeTheImportAccountTo->schoolLocation);
                return $this->handleMatchingTeachersInKoepel($userWhereWeWouldLikeToMergeTheImportAccountTo,
                    $this->laravelUser);
            }
        }
        $url = route('auth.login', [
            'tab' => 'entree', 'entree_error_message' => 'auth.email_already_in_use_in_different_school_location'
        ]);

        return $this->redirectToUrlAndExit($url);
    }
}
