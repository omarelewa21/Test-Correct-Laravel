<?php

namespace tcCore\Http\Livewire;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Livewire\Component;
use tcCore\User;

class PasswordReset extends Component
{
    public $password;
    public $password_confirmation;
    public $username;
    public $token;

    public $showSuccessModal = false;

    public $btnDisabled = false;

    protected $queryString = ['token'];

    protected $messages = [
        'password.required' => 'Wachtwoord is verplicht',
        'password.min'      => 'Wachtwoord moet langer zijn dan 8 karakters',
        'password.regex'    => 'Wachtwoord voldoet niet aan de eisen',
        'password.same'     => 'Wachtwoord komt niet overeen',
    ];


    public function getMinCharRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return mb_strlen($this->password) < 8 ? false : true;
        }
    }

    public function getMinDigitRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return preg_match('/\d/', $this->password) ? true : false;
        }
    }

    public function getSpecialCharRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return preg_match('/[^a-zA-Z\d]/', $this->password) ? true : false;
        }
    }

    public function rules()
    {
        return [
            'username' => 'required|email',
            'password' => 'required|min:8|regex:/\d/|regex:/[^a-zA-Z\d]/|same:password_confirmation',
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
        });

        if ($response === PasswordBroker::PASSWORD_RESET){
            $this->showSuccessModal = true;
        }

        if ($response === PasswordBroker::INVALID_USER) {
            $this->addError('password', 'Het opgegeven emailadres is niet correct');
        };

        if ($response === PasswordBroker::INVALID_TOKEN) {
                $this->addError('password', 'De gebruikte link niet correct, of verlopen');
        }
    }

    public function redirectToLogin()
    {
        $this->redirect(config('app.url_login'));
    }

    public function render()
    {
        return view('livewire.password-reset')->layout('layouts.onboarding');
    }
}
