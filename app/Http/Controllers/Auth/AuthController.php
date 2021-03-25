<?php

namespace tcCore\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use tcCore\FailedLogin;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\User\Roles;
use tcCore\LoginLog;
use tcCore\User;
use tcCore\Jobs\SetSchoolYearForDemoClassToCurrent;

class AuthController extends Controller
{

    protected $text2speechPriceRoles = ['Teacher','Administrator','School manager','School management','Mentor'];

    function __construct(User $user, Guard $auth)
    {
        $this->user = $user;
        $this->auth = $auth;
    }

    public function doWeNeedCaptcha(Request $request)
    {
        if(FailedLogin::doWeNeedExtraSecurityLayer($request->get('username'))){
            return \Response::make(true, 200);
        }
        return \Response::make(false,200);
    }

    public function getApiKey(Request $request)
    {
        $user = $request->get('user');
        $password = $request->get('password');
        $captcha = $request->get('captcha');
        $ip = $request->get('ip');

        if(FailedLogin::doWeNeedExtraSecurityLayer($user) && !$captcha){
            return \Response::make("NEEDS_CAPTCHA", 403);
        }

        if ($this->auth->once(['username' => $user, 'password' => $password])) {
            $user = $this->auth->user();
            $user->setAttribute('session_hash', $user->generateSessionHash());
            if((bool) $user->demo === true){
                $user->demoRestrictionOverrule = true;
            }
            $user->save();
            $user->load('roles');

            $hidden = $user->getHidden();

            if (($key = array_search('api_key', $hidden)) !== false) {
                unset($hidden[$key]);
            }
            if (($key = array_search('session_hash', $hidden)) !== false) {
                unset($hidden[$key]);
            }

            if($user->isA('teacher')){
                (new DemoHelper())->createDemoForTeacherIfNeeded($user);
                $this->dispatch(new SetSchoolYearForDemoClassToCurrent($user->schoolLocation));
            }


            if($this->canHaveGeneralText2SpeechPrice($user)){
                $user->setAttribute('general_text2speech_price',config('custom.text2speech.price'));
            }
            $user->setAttribute('isToetsenbakker',$user->isToetsenbakker());

            $user->setAttribute('hasCitoToetsen',$user->hasCitoToetsen());

            $user->setAttribute('hasSharedSections',$user->hasSharedSections());

            $user->makeOnboardWizardIfNeeded();

            $clone = $user->replicate();
            $clone->{$user->getKeyName()} = $user->getKey();
            $clone->setHidden($hidden);

            $clone->logins = $user->getLoginLogCount();
            $clone->is_temp_teacher = $user->getIsTempTeacher();
            LoginLog::create(['user_id' => $user->getKey()]);
            FailedLogin::solveForUsernameAndIp($user,$ip);
            return new JsonResponse($clone);
        } else {
            FailedLogin::create([
               'username' => $user,
               'ip' => $ip
            ]);
            return \Response::make("Invalid credentials.", 403);
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
