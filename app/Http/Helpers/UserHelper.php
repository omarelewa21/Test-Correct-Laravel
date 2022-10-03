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
use tcCore\TemporaryLogin;
use tcCore\User;

class UserHelper
{

    protected $text2speechPriceRoles = ['Teacher','Administrator','School manager','School management','Mentor'];

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
        if((bool) $user->demo === true){
            $user->demoRestrictionOverrule = true;
        }
        $user->save();
        $user->load('roles');

        if(($user->isA('teacher') || $user->isA('student')) && !$throughTempLogin && EntreeHelper::shouldPromptForEntree($user)){
            return \Response::make("NEEDS_LOGIN_ENTREE",403);
        }

        if($schoolLocation = $user->schoolLocation) {
            session()->put('locale', $schoolLocation->school_language);
            app()->setLocale(session('locale'));
        }

        $hidden = $user->getHidden();

        if (($key = array_search('api_key', $hidden)) !== false) {
            unset($hidden[$key]);
        }
        if (($key = array_search('session_hash', $hidden)) !== false) {
            unset($hidden[$key]);
        }

        if($user->isA('teacher')){
            (new DemoHelper())->createDemoForTeacherIfNeeded($user);
            dispatch(new SetSchoolYearForDemoClassToCurrent($user->schoolLocation));
        }

        if($this->canHaveGeneralText2SpeechPrice($user)){
            $user->setAttribute('general_text2speech_price',config('custom.text2speech.price'));
        }
        $user->setAttribute('isToetsenbakker',$user->isToetsenbakker());

        $user->setAttribute('hasCitoToetsen',$user->hasCitoToetsen());

        $user->setAttribute('hasSharedSections',$user->hasSharedSections());

        $user->setAttribute('temporaryLoginOptions', TemporaryLogin::getOptionsForUser($user));

        $user->makeOnboardWizardIfNeeded();
        $user->createGeneralTermsLogIfRequired();
        $user->createTrialPeriodRecordIfRequired();

        $clone = $user->replicate();
        $clone->{$user->getKeyName()} = $user->getKey();
        $clone->setHidden($hidden);

        $clone->logins = $user->getLoginLogCount();
        $clone->is_temp_teacher = $user->getIsTempTeacher();
        LoginLog::create(['user_id' => $user->getKey()]);
        if($ip) {
            FailedLogin::solveForUsernameAndIp($user->username, $ip);
        }
        return new JsonResponse($clone);
    }

    public function createUserFromData($data){
        $userFactory = new Factory(new User());

        $user = $userFactory->generate($data,true);

        if($user->invited_by != null){
            if(!$user->emailDomainInviterAndInviteeAreEqual()) {
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

    protected function canHaveGeneralText2SpeechPrice($user){
        $roles = Roles::getUserRoles($user);
        foreach($roles as $role){
            if(in_array($role,$this->text2speechPriceRoles)) {
                return true;
            }
        }
        return false;
    }
}