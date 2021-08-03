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
use tcCore\SamlMessage;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class EntreeHelper
{
    private $attr;

    private $messageId;

    private $location = null;

    public $laravelUser = null;

    public $shouldThrowAnErrorDuringTransaction = false;

    private $brinFourErrorDetected = false;

    protected $rolesToTransformToTeacher = ['employee'];

    public function __construct($attr, $messageId)
    {
        $this->attr = $this->transformAttributesIfNeededAndReturn($attr);
        $this->messageId = $messageId;
    }

    protected function transformAttributesIfNeededAndReturn($attr)
    {
        // we may get employee, then we transfer it to teacher
        if (array_key_exists('eduPersonAffiliation', $attr) && in_array(strtolower($attr['eduPersonAffiliation'][0]), $this->rolesToTransformToTeacher)) {
            $attr['eduPersonAffiliation'][0] = 'teacher';
        }

        return $attr;
    }

    public function redirectIfBrinUnknown()
    {
        $this->setLocationWithSamlAttributes();
        if ($this->location == null) {
            $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.brin_not_found']);
            if ($this->brinFourErrorDetected) {
                $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.brin_four_detected']);
            }
            return $this->redirectToUrlAndExit($url);
        }
        return $this->location;
    }

    private function setLocationWithSamlAttributes()
    {
        if(null !== $this->location){
            // we did run this method before
            return true;
        }

        $brinZesCode = $this->getBrinFromAttributes();

        $external_main_code = substr($brinZesCode, 0, 4);
        if (strlen($brinZesCode) === 6) {
            $external_sub_code = substr($brinZesCode, 4, 2);
            $this->location = SchoolLocation::where('external_main_code', $external_main_code)
                ->where('external_sub_code', $external_sub_code)
                ->first();
            return true;
        }
        if (strlen($brinZesCode) === 4) {
            // 1. zoeken binnen de scholengemeenschap (is school)
            // 2. schoollocaties zoeken binnen deze scholengemeenschap die voldoen omdat ze ook een entree koppeling hebben
            // 3. is er een locatie die ook past bij de gebruiker
            // 4. zo ja bij voorkeur die pakken die nu ook al actief is en anders de eerste die wel voldoet
            // 5. indien geen gevonden dan brinFourErrorDetected
            $school = School::where('external_main_code', $external_main_code)->get();
            if ($school->count() === 1) {
                $locations = SchoolLocation::where('school_id', $school->first()->getKey())
                    ->where('sso_type', SchoolLocation::SSO_ENTREE)
                    ->where('sso_active', 1)
                    ->get();
                if ($locations->count() > 0) {
                    if($this->isTeacherBasedOnAttributes()){
                        // teacher (later on there will be a match on role)
                        $allowedLocations = $locations->filter(function(SchoolLocation $sl){
                            return User::findByEckidAndSchoolLocationIdForTeacher($this->getEckIdFromAttributes(), $sl->getKey())->first() != null;
                        });
                        if($allowedLocations->count() > 0){
                            // the locations for which the teacher is allowed to access
                            if($allowedLocations->count() === 1){
                                $this->location = $allowedLocations->first();
                            } else {
                                // search the school location where the teacher was logged in last if available
                                $lastLocation = $allowedLocations->first(function(SchoolLocation $sl){
                                    return User::findByEckidAndSchoolLocationIdForTeacher($this->getEckIdFromAttributes(), $sl->getKey())
                                        ->where('users.school_location_id',$sl->getKey())->first() != null;
                                });
                                if($lastLocation->count() === 1){
                                    // there was a location for which the teacher was logged in last
                                    $this->location = $lastLocation->first();
                                } else {
                                    // sorry not available so we just take the first we can find
                                    $this->location = $allowedLocations->first();
                                }
                            }
                        }
                    } else {
                        // student (later on there will be a match on role)
                        $allowedLocations = $locations->filter(function(SchoolLocation $sl){
                            return User::scopeFindByEckidAndSchoolLocationIdForUser($this->getEckIdFromAttributes(), $sl->getKey())->first() != null;
                        });
                        if($allowedLocations->count() > 0){
                            // the locations for which the user is allowed to access
                            if($allowedLocations->count() === 1){
                                $this->location = $allowedLocations->first();
                            } else {
                                // this should never happen
                                $this->location = $allowedLocations->first();
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

        }
        $this->brinFourErrorDetected = true;
    }

    public static function shouldPromptForEntree(User $user)
    {
        return (optional($user->schoolLocation)->lvs_active && empty($user->eck_id));
    }

    private function getEckIdFromAttributes()
    {
        return $this->attr['eckId'][0];
    }

    private function getBrinFromAttributes()
    {
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
        if (array_key_exists('mail',
                $this->attr) && $this->attr['mail'][0]) {
            return $this->attr['mail'][0];
        }
        return null;
    }

    private function getRoleFromAttributes()
    {
        if (array_key_exists('eduPersonAffiliation',
                $this->attr) && $this->attr['eduPersonAffiliation'][0]) {
            return $this->attr['eduPersonAffiliation'][0];
        }
        return null;
    }

    private function createSamlMessage()
    {
        $this->validateAttributes();

        return SamlMessage::create([
            'message_id' => $this->messageId,
            'eck_id' => Crypt::encryptString($this->getEckIdFromAttributes()),
            'email' => $this->attr['mail'][0],
        ]);
    }

    private function validateAttributes()
    {
        if (!array_key_exists('eckId', $this->attr) || !array_key_exists(0, $this->attr['eckId'])) {
            throw new \Exception('no eckId found in saml request');
        }

        if (!array_key_exists('mail', $this->attr) || !array_key_exists(0, $this->attr['mail'])) {
            throw new \Exception('no mail found in saml request');
        }
    }

    public function blockIfReplayAttackDetected()
    {
        $message = SamlMessage::whereMessageId($this->messageId)->first();
        if ($message) {
            dd('preventing reuse of messageId');
        }
    }

    public function redirectIfscenario5()
    {
        if (!empty($this->location->lvs_type)) {
            return true;
        }
        if ($this->location->lvs_active) {
            return true;
        }
        $this->handleScenario5();
    }

    public function handleScenario5()
    {
        $this->validateAttributes();
        if ($url = $this->redirectIfBrinNotSso()) {
            return $url;
        }

        if ($this->isTeacherBasedOnAttributes()) {
            $this->laravelUser = User::findByEckidAndSchoolLocationIdForTeacher($this->getEckIdFromAttributes(), $this->location->getKey())->first();
        } else {
            $this->laravelUser = User::findByEckidAndSchoolLocationIdForUser($this->getEckIdFromAttributes(), $this->location->getKey())->first();
        }

//        $this->laravelUser = User::findByEckId($this->attr['eckId'][0])->first();
        if ($this->laravelUser) {
            // return true is hier waarschijnlijk voldoende omdat je dan via scenario 1 wordt ingelogged;
            $this->handleUpdateUserWithSamlAttributes();
            $url = $this->laravelUser->getTemporaryCakeLoginUrl();
            return $this->redirectToUrlAndExit($url);
        }
// redirect to maak koppelingscherm;

        $message = $this->createSamlMessage();
        $url = route('auth.login', ['tab' => 'entree', 'uuid' => $message->uuid]);
        return $this->redirectToUrlAndExit($url);
    }

    protected function isTeacherBasedOnAttributes()
    {
        return !! strtolower($this->getRoleFromAttributes()) == 'teacher';
    }

    public function redirectIfBrinNotSso()
    {
        $this->setLocationWithSamlAttributes();
        if ($this->location->sso_active != 1) {
            $url = route('auth.login',
                ['tab' => 'login', 'entree_error_message' => 'auth.school_not_registered_for_sso']);
            return $this->redirectToUrlAndExit($url);
        }
    }


    public function redirectIfNoUserWasFoundForEckId()
    {
        $this->validateAttributes();
        $this->setLaravelUser();

        if ($this->laravelUser) {
            return true;
        }

        $url = route('auth.login',
            ['tab' => 'entree', 'entree_error_message' => 'auth.school_info_not_synced_with_test_correct']);
        return $this->redirectToUrlAndExit($url);
    }

    public function redirectIfUserNotInSameSchool()
    {
        $this->validateAttributes();
        if (null == $this->location) {
            $this->setLocationWithSamlAttributes();
        }
        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }
        if ($this->location && $this->location->is(optional($this->laravelUser)->schoolLocation)) {
            return true;
        }

        $url = route('auth.login', ['tab' => 'entree', 'entree_error_message' => 'auth.user_not_in_same_school']);

        return $this->redirectToUrlAndExit($url);
    }

    public function redirectIfUserNotHasSameRole()
    {
        $this->validateAttributes();

        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }

        if(null == $this->laravelUser){
            $this->redirectIfNoUserWasFoundForEckId();
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
        logger($functionName);
        logger('id of laravel user ' . optional($this->laravelUser)->getKey());
        $this->attr['eckId'][0] = substr($this->attr['eckId'][0], -10);
        logger($this->attr);
    }

    public function handleScenario1()
    {
        $this->validateAttributes();

        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }

        if (null == $this->laravelUser) {
            $this->addLogRows('handleScenario1');
            $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.roles_do_not_match_up']);
            return $this->redirectToUrlAndExit($url);
        }

        $this->handleUpdateUserWithSamlAttributes();
        $url = $this->laravelUser->getTemporaryCakeLoginUrl();
        return $this->redirectToUrlAndExit($url);

    }

    public function handleScenario2IfAddressIsKnownInOtherAccount()
    {
        $this->validateAttributes();

        if (null == $this->laravelUser) {
            $this->setLaravelUser();
        }

        if (null == $this->laravelUser) {
            $this->addLogRows('handleScenario2IfAddressIsKnownInOtherAccount');
            $url = route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.roles_do_not_match_up']);
            return $this->redirectToUrlAndExit($url);
        }

        $otherUserWithEmailAddress = User::where('username', $this->getEmailFromAttributes())
            ->where('id', '<>', $this->laravelUser->id)
            ->first();
        if ($otherUserWithEmailAddress) {
            if ($this->laravelUser->isA('Student')) {
                if (!$this->laravelUser->inSchoolLocationAsUser($otherUserWithEmailAddress)) {
                    $url = route('auth.login', [
                        'tab' => 'entree',
                        'entree_error_message' => 'auth.student_account_not_found_in_this_location'
                    ]);
                    return $this->redirectToUrlAndExit($url);
                } else {
                    return $this->handleMatchingWithinSchoolLocation($otherUserWithEmailAddress, $this->laravelUser);
                }
            } elseif ($this->laravelUser->isA('Teacher') && $otherUserWithEmailAddress->isA('Teacher')) {
                ActingAsHelper::getInstance()->setUser($otherUserWithEmailAddress);
                if ($this->laravelUser->inSchoolLocationAsUser($otherUserWithEmailAddress)) {
                    return $this->handleMatchingWithinSchoolLocation($otherUserWithEmailAddress, $this->laravelUser);
                } elseif ($this->laravelUser->inSameKoepelAsUser($otherUserWithEmailAddress)) {
                    return $this->handleMatchingTeachersInKoepel($otherUserWithEmailAddress, $this->laravelUser);
                }
            }
            $url = route('auth.login', [
                'tab' => 'entree', 'entree_error_message' => 'auth.email_already_in_use_in_different_school_location'
            ]);

            if (App::runningUnitTests()) {
                return $url;
            }
            header('Location: $url');
            exit;
        }

        return false;
    }

    private function handleMatchingWithinSchoolLocation(User $oldUser, User $user)
    {
        try {
            DB::beginTransaction();
            $this->copyEckIdNameNameSuffixNameFirstAndTransferClassesAndDeleteUser($oldUser, $user);
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

    private function copyEckIdNameNameSuffixNameFirstAndTransferClassesAndDeleteUser(User $oldUser, User $user)
    {
        $eckId = $user->eckId;
        $user->removeEckId();
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
        if ($oldUser->isA('teacher')) {
            try {
                DB::beginTransaction();
                $oldUser->addSchoolLocation($user->schoolLocation);
                $this->copyEckIdNameNameSuffixNameFirstAndTransferClassesAndDeleteUser($oldUser, $user);
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
        $emailFromEntree = false;
        if (array_key_exists('mail', $this->attr) && is_array($this->attr['mail'])) {
            $emailFromEntree = array_pop($this->attr['mail']);
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
        if (App::runningUnitTests()) {
            return $url;
        }
        header("Location: $url");
        exit;
    }

    public function setLaravelUser(): void
    {
        if (strtolower($this->getRoleFromAttributes()) == 'teacher') {
            $this->laravelUser = User::findByEckidAndSchoolLocationIdForTeacher($this->getEckIdFromAttributes(), $this->location->getKey())->first();
        } else {
            $this->laravelUser = User::findByEckidAndSchoolLocationIdForUser($this->getEckIdFromAttributes(), $this->location->getKey())->first();
        }
    }


}
