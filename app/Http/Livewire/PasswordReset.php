<?php

namespace tcCore\Http\Livewire;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Password;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\Auth\Login;
use tcCore\Http\Traits\UserNotificationForController;
use tcCore\Rules\NistPasswordRules;
use tcCore\User;

class PasswordReset extends TCComponent
{
    use UserNotificationForController;

    public $password;
    public $password_confirmation;
    public $username;
    public $token;

    public $showSuccessModal = false;

    public $btnDisabled = false;

    protected $queryString = ['token'];
    protected $preventFieldTransformation = ['password', 'password_confirmation'];


    private function get_browser_language()
    {
        return BaseHelper::browserLanguage();
    }

    protected function messages()
    {
        App::setLocale('NL');

        return [
            'username.required'  => __('auth.email_required'),
            'username.email'     => __('auth.email_incorrect'),
            'password.required'  => __('auth.password_required'),
            'password.min'       => __('auth.password_min'),
            'password.same'      => __('registration.password_same'),
            'password.confirmed' => __('registration.password_same'),
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
            'password' => NistPasswordRules::changePassword($this->username),
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
            $user->password = $password;
            $user->save();
        });

        if ($response === PasswordBroker::PASSWORD_RESET) {
            $this->notifyUser($this->username);
            $this->emitTo(Login::class, 'password_reset');
        }

        if ($response === PasswordBroker::INVALID_USER) {
            if ($this->get_browser_language() == 'nl') {
                $this->addError('password', 'Het opgegeven emailadres is niet correct');
            } else {
                $this->addError('password', 'The email address provided is incorrect');
            }
        };

        if ($response === PasswordBroker::INVALID_TOKEN) {
            if ($this->get_browser_language() == 'nl') {
                $this->addError('password', 'De gebruikte link niet correct, of verlopen');
            } else {
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
