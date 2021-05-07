<?php

namespace tcCore\Http\Livewire\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\FailedLogin;
use tcCore\TemporaryLogin;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $captcha = '';

    public $firstName = '';
    public $suffix = '';
    public $lastName = '';

    public $forgotPasswordEmail = '';

    public $requireCaptcha = false;
    public $testTakeCode = [];

    public $loginTab = true;
    public $showTestCode = false;
    public $loginButtonDisabled = true;
    public $guestLoginButtonDisabled = true;
    public $forgotPasswordButtonDisabled = true;

    protected $rules = [
        'username' => 'required|email',
        'password' => 'required',
    ];

    protected function messages(): array
    {
        return [
            'password.required' => __('auth.password_required'),
            'username.required' => __('auth.email_required'),
            'username.email'    => __('auth.email_incorrect'),
        ];
    }


    public function mount()
    {
//        if (Auth::check()) {
//            return redirect()->intended(route('student.dashboard'));
//        }
    }

    public function login()
    {
        $this->resetErrorBag();

        $credentials = $this->validate();

        if (!$this->captcha && FailedLogin::doWeNeedExtraSecurityLayer($this->username)) {
            return $this->requireCaptcha = true;
        }
        if ($this->captcha) {
            $this->validateCaptcha();
        }

        if (!auth()->attempt($credentials)) {
            $this->createFailedLogin();
            return $this->addError('invalid_user', __('auth.failed'));
        }

        $this->doLoginProcedure();

        if (auth()->user()->isA('Student')) {
            return redirect()->intended(route('student.dashboard'));
        }
        auth()->user()->redirectToCakeWithTemporaryLogin();
    }

    public function guestLogin()
    {
        if($this->isTestTakeCodeValid()) {
            dd('Helemaal goed');
        }
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.auth');
    }

    public function goToPasswordReset()
    {
        $this->redirect(route('password.reset'));
    }

    private function doLoginProcedure()
    {
        $sessionHash = auth()->user()->generateSessionHash();
        session()->put('session_hash', $sessionHash);
        auth()->user()->setAttribute('session_hash', $sessionHash);
        auth()->user()->save();
        FailedLogin::solveForUsernameAndIp($this->username, request()->ip());
    }

    private function createFailedLogin()
    {
        FailedLogin::create([
            'username' => $this->username,
            'ip'       => request()->ip()
        ]);
    }

    private function validateCaptcha(): void
    {
        $validateCaptcha = Validator::make(['captcha' => $this->captcha], ['captcha' => 'required|captcha']);

        if ($validateCaptcha->fails()) {
            $this->reset('captcha');
            $this->dispatchBrowserEvent('refresh-captcha');
        }

        $rulesWithCaptcha = array_merge($this->rules, ['captcha' => 'required|captcha']);
        $this->validate($rulesWithCaptcha);
    }

    public function updated($name, $value)
    {
        if ($this->couldBeEmail($this->username) && !blank($this->password)) {
            $this->loginButtonDisabled = false;

            if ($this->showTestCode) {
                $this->loginButtonDisabled = true;
                if (count($this->testTakeCode) == 6) {
                    $this->loginButtonDisabled = false;
                }
            }
        } else {
            $this->loginButtonDisabled = true;
        }

        $this->couldBeEmail($this->forgotPasswordEmail) ? $this->forgotPasswordButtonDisabled = false : $this->forgotPasswordButtonDisabled = true;

        $this->guestLoginButtonDisabled = !(!blank($this->firstName) && !blank($this->lastName) && count($this->testTakeCode) == 6);

    }


    public function updatedShowTestCode($value)
    {
        if (!$value) {
            $this->testTakeCode = [];
        }
    }

    private function couldBeEmail(string $email): bool
    {
        return Str::of($email)->containsAll(['@', '.']);
    }

    public function sendForgotPasswordEmail()
    {
        dd('Verzend de mail naar: ' . $this->forgotPasswordEmail);
    }

    private function isTestTakeCodeValid(): bool
    {
        foreach ($this->testTakeCode as $key => $value){
            if (!$value) {
                $this->addError('invalid_test_code', __('auth.test_code_invalid'));
                return false;
            }
        }
        return true;
    }
}
