<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use tcCore\Answer;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\FailedLogin;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Jobs\SetSchoolYearForDemoClassToCurrent;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\LoginLog;
use tcCore\Student;
use tcCore\TemporaryLogin;
use tcCore\User;

class UserHelper
{

    const TEXT2SPEECH_PRICE_ROLES = ['Teacher', 'Administrator', 'School manager', 'School management', 'Mentor'];

    public static function logout()
    {
        Auth::user()->session_hash = '';
        Auth::user()->save();

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function handleAfterLoginValidation($user, $throughTempLogin = false, $ip = false)
    {
        $user->setAttribute('session_hash', $user->generateSessionHash());
        if ((bool)$user->demo) {
            $user->demoRestrictionOverrule = true;
        }
        $user->save();
        $user->load('roles');

        if (($user->isA('teacher') || $user->isA('student')) && !$throughTempLogin && EntreeHelper::shouldPromptForEntree($user)) {
            return \Response::make("NEEDS_LOGIN_ENTREE", 403);
        }

        if ($user->schoolLocation) {
            session()->put('locale', $user->schoolLocation->school_language);
            app()->setLocale(session('locale'));
        }

        $hidden = self::getHiddenUserProperties($user);
        self::setAdditionalUserAttributes($user);
        self::handleTeacherEnvironment($user);

        $clone = $this->getUserClone($user, $hidden);
        LoginLog::create(['user_id' => $user->getKey()]);
        if ($ip) {
            FailedLogin::solveForUsernameAndIp($user->username, $ip);
        }
        return new JsonResponse($clone);
    }

    public function createUserFromData($data)
    {
        $userFactory = new Factory(new User());

        $user = $userFactory->generate($data, true);

        if ($user->invited_by != null) {
            if (!$user->emailDomainInviterAndInviteeAreEqual()) {
                $schoolLocationId = SchoolHelper::getTempTeachersSchoolLocation()->getKey();
                ActingAsHelper::getInstance()->setUser(SchoolHelper::getSomeTeacherOrSchoolManagerBySchoolLocationId($schoolLocationId));
                $user->school_location_id = $schoolLocationId;
            }
        }

        if ($user->save() !== false) {
            if (isset($data['send_welcome_mail']) && $data['send_welcome_mail'] == true) {
                dispatch_now(new SendWelcomeMail($user->getKey(), isset($data['url']) ? $data['url'] : false));
            }
            return $user;
        } else {
            return false;
        }
    }

    /**
     * @param $user
     * @return mixed
     */
    private function getHiddenUserProperties($user)
    {
        $hidden = $user->getHidden();

        if (($key = array_search('api_key', $hidden)) !== false) {
            unset($hidden[$key]);
        }
        if (($key = array_search('session_hash', $hidden)) !== false) {
            unset($hidden[$key]);
        }
        return $hidden;
    }

    public static function setAdditionalUserAttributes(User $user)
    {
        if ($user->canHaveGeneralText2SpeechPrice()) {
            $user->setAttribute('general_text2speech_price', config('custom.text2speech.price'));
        }
        $user->setAttribute('isToetsenbakker', $user->isToetsenbakker());

        $user->setAttribute('hasCitoToetsen', $user->hasCitoToetsen());

        $user->setAttribute('hasSharedSections', $user->hasSharedSections());

        $user->setAttribute('isExamCoordinator', $user->isValidExamCoordinator());

        $user->setAttribute('temporaryLoginOptions', TemporaryLogin::getOptionsForUser($user));
    }

    /**
     * @param $user
     * @return void
     */
    public static function handleTeacherEnvironment($user): void
    {
        if (!$user->isA('teacher')) return;

//        (new DemoHelper())->createDemoForTeacherIfNeeded($user, true);

        $user->makeOnboardWizardIfNeeded();

        $user->createGeneralTermsLogIfRequired();
        $user->createTrialPeriodRecordIfRequired();
    }

    /**
     * @param $user
     * @param $hidden
     * @return mixed
     */
    public static function getUserClone($user, $hidden)
    {
        $clone = $user->replicate();
        $clone->{$user->getKeyName()} = $user->getKey();
        $clone->setHidden($hidden);

        $clone->logins = $user->getLoginLogCount();
        $clone->is_temp_teacher = $user->getIsTempTeacher();
        return $clone;
    }

    public static function resetPasswordToExternalIdForClassIds($classIds = null)
    {
        if(null === $classIds){
            return;
        }
        $results = (object) [
          'success' => [],
          'error' => []
        ];
        User::whereIn('id',Student::whereClassId($classIds)->select('user_id'))
            ->get()
            ->each(function (User $user) use($results){
                if(app()->runningInConsole()) {
                    echo 'userFound ' . $user->username . PHP_EOL;
                }
                if(!$user->isA('student')){
                    if(app()->runningInConsole()) {
                        echo 'not a student ' . PHP_EOL;
                    }
                    $results->error[] = $user->username;
                    return;
                }
                $externalId = null;
                if($user->external_id){
                    if(app()->runningInConsole()) {
                        echo 'externalId found  ' . $user->external_id . PHP_EOL;
                    }
                    $externalId = $user->external_id;
                } else {
                   list($_externalId,$domain) = explode('@',$user->username);
                   if(is_numeric($_externalId)){
                       $externalId = $_externalId;
                       if(app()->runningInConsole()) {
                           echo 'externalId found through username ' . $externalId . PHP_EOL;
                       }
                   } else {
                       if(app()->runningInConsole()) {
                           echo 'externalId not found through email ' . PHP_EOL;
                       }
                       $results->error[] = $user->username;
                       return;
                   }
                }
                $results->success[] = $user->username;
                User::whereId($user->getKey())->update(['password' => bcrypt($externalId)]);
                if(app()->runningInConsole()) {
                    echo 'password set for  ' . $user->username . ' => ' . $externalId . PHP_EOL;
                }
            });
        return $results;
    }
}