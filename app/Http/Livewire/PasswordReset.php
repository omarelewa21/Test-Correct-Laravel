<?php

namespace tcCore\Http\Livewire;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Livewire\Component;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\UserNotificationForController;
use tcCore\User;

class PasswordReset extends Component
{
    use UserNotificationForController;

    public $password;
    public $password_confirmation;
    public $username;
    public $token;

    public $showSuccessModal = false;

    public $btnDisabled = false;

    protected $queryString = ['token'];

    private function get_browser_language(){
        if(array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)){
            $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if($language ==	 'nl'){
                return 'nl';
            }
        }
        return 'en';
    }

    protected function messages(){
        if($this->get_browser_language() == 'nl'){
            return[
                'password.required' => 'Wachtwoord is verplicht',
                'password.min'      => 'Wachtwoord moet langer zijn dan 8 karakters',
                'password.same'     => 'Wachtwoord komt niet overeen',
            ];
        }
        return[
            'password.required' => 'Password is required',
            'password.min'      => 'Password must be longer than 8 characters',
            'password.same'     => 'Password does not match',
        ];
    }


    public function getMinCharRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return mb_strlen($this->password) < 8 ? false : true;
        }
    }

    public function rules()
    {
        return [
            'username' => 'required|email',
            'password' => 'required|same:password_confirmation|'. User::getPasswordLengthRule(),
            'token'    => 'required',
        ];
    }


    public function resetPassword()
    {
        $this->validate();

        $credentials = [
            'password_confirmation' => $this->password_confirmation,
            'password'              => $this->password,
            'token'                 => $this->token,
            'username'              => $this->username,
        ];


        $response = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
            dd(\Hash::check('Welkom456', $user->password));
        });

        if ($response === PasswordBroker::PASSWORD_RESET){
            $this->notifyUser($this->username);
            $this->showSuccessModal = true;
        }

        if ($response === PasswordBroker::INVALID_USER) {
            if($this->get_browser_language() == 'nl'){
                $this->addError('password', 'Het opgegeven emailadres is niet correct');
            }
            else{
                $this->addError('password', 'The email address provided is incorrect');
            }
        };

        if ($response === PasswordBroker::INVALID_TOKEN) {
            if($this->get_browser_language() == 'nl'){
                $this->addError('password', 'De gebruikte link niet correct, of verlopen');
            }
            else{
                $this->addError('password', 'The link used is incorrect, or has expired');
            }
                
        }
    }

    public function redirectToLogin()
    {
        $this->redirect(BaseHelper::getLoginUrl());
    }

    public function render()
    {
        return view('livewire.password-reset')->layout('layouts.onboarding');
    }

    protected function notifyUser($userName)
    {
        try {
            $user = User::where('username', $userName)->firstOrFail();
            $this->sendPasswordChangedMail($user);
        } catch (\Exception $e) {
            //silent fail
        }
    }
}
