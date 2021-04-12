<?php

namespace tcCore\Http\Livewire\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\FailedLogin;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $captcha = '';
    public $requireCaptcha = false;

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
        if (Auth::check()) {
            return redirect()->intended(route('student.dashboard'));
        }
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

        if (auth()->attempt($credentials)) {
            $this->doLoginProcedure();
            return redirect()->intended(route('student.dashboard'));
        }

        $this->createFailedLogin();
        return $this->addError('invalid_user', __('auth.failed'));
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
}
