<?php

namespace tcCore\Http\Livewire\Auth;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\FailedLogin;
use tcCore\Jobs\SendForgotPasswordMail;
use tcCore\User;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $captcha = '';

    public $firstName = '';
    public $suffix = '';
    public $lastName = '';

    public $forgotPasswordEmail = '';
    public $entreeEmail = '';
    public $entreePassword = '';

    public $requireCaptcha = false;
    public $testTakeCode = [];

    protected $queryString = ['tab'];

    public $tab = 'login';

//    public $loginTab = true;
//    public $forgotPasswordTab = false;
//    public $entreeTab = false;

    public $showTestCode = false;
    public $loginButtonDisabled = true;
    public $guestLoginButtonDisabled = true;
    public $forgotPasswordButtonDisabled = true;
    public $connectEntreeButtonDisabled = true;

    public $showAuthModal = false;
    public $authModalRoleType;
    public $showSendForgotPasswordNotification = false;

    public $studentDownloadUrl = 'https://www.test-correct.nl/student/';

    protected $listeners = ['open-auth-modal' => 'openAuthModal'];

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
        $samlAttr = Session::get('saml_attributes');
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        Session::set('saml_attributes', $samlAttr);
    }

    public function login()
    {
        $this->resetErrorBag();

        if (!$this->captcha && FailedLogin::doWeNeedExtraSecurityLayer($this->username)) {
            $this->requireCaptcha = true;
            return;
        }
        if ($this->captcha && !$this->validateCaptcha()) {
            return;
        }

        $credentials = $this->validate();
        if (!auth()->attempt($credentials)) {
            if($this->requireCaptcha) {
                $this->reset('captcha');
                $this->emit('refresh-captcha');
                return;
            }
            $this->createFailedLogin();
            $this->addError('invalid_user', __('auth.failed'));
            return;
        }

        $this->doLoginProcedure();

        if (auth()->user()->isA('Student')) {
            return redirect()->intended(route('student.dashboard'));
        }
        if (auth()->user()->isA('Account manager')) {
            return redirect()->intended(route('uwlr.grid'));
        }

        auth()->user()->redirectToCakeWithTemporaryLogin();
    }

    public function guestLogin()
    {
//        if($this->isTestTakeCodeValid()) {
//
//        }
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

    private function validateCaptcha()
    {
        $validateCaptcha = Validator::make(['captcha' => $this->captcha], ['captcha' => 'required|captcha']);

        if ($validateCaptcha->fails()) {
            $this->reset('captcha');
            $this->addError('captcha', __('auth.incorrect_captcha'));
            $this->emit('refresh-captcha');
            return false;
        }

        $rulesWithCaptcha = array_merge($this->rules, ['captcha' => 'required|captcha']);
        $this->validate($rulesWithCaptcha);
        return true;
    }

    public function updated($name, $value)
    {
        $this->checkLoginFieldsForInput();

        $this->couldBeEmail($this->forgotPasswordEmail) ? $this->forgotPasswordButtonDisabled = false : $this->forgotPasswordButtonDisabled = true;

        $this->guestLoginButtonDisabled = !(filled($this->firstName) && filled($this->lastName) && count($this->testTakeCode) == 6);

        if($this->couldBeEmail($this->entreeEmail) && filled($this->entreePassword)) {
            $this->connectEntreeButtonDisabled = false;
        }

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
        $user = User::whereUsername($this->forgotPasswordEmail)->first();
        if ($user) {
            $token = Password::getRepository()->create($user);
            $url = sprintf('%spassword-reset/?token=%%s',config('app.base_url'));
            $urlLogin = route('auth.login');

            try {
                Mail::to($user->username)->send(new SendForgotPasswordMail($user,$token,$url,$urlLogin));
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
            }
        }

        $this->showSendForgotPasswordNotification = true;
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

    public function openAuthModal()
    {
        $this->showAuthModal = true;
    }

    public function setAuthModalRoleType($value)
    {
        $this->authModalRoleType = $value;
    }

    public function createAccountRedirect()
    {
        if (blank($this->authModalRoleType)) {
            return;
        }

        if ($this->authModalRoleType === 'student') {
            return redirect($this->studentDownloadUrl);
        }
        return redirect(route('onboarding.welcome'));
    }

    public function checkLoginFieldsForInput()
    {
        if ($this->couldBeEmail($this->username) && filled($this->password)) {
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
    }


    public function entreeForm()
    {
        $credentials = [
            'username' => $this->entreeEmail,
            'password' => $this->entreePassword,
        ];

        dump(Session::all());

        if (!Session::has('saml_attributes')) {
            return $this->addError('invalid_user_pfff', __('auth.failed'));
        }

        if (!auth()->attempt($credentials)) {
            $this->createFailedLogin();
            return $this->addError('invalid_user', __('auth.failed'));
        }
        $user = auth::user();

        if ($user->eckId !== null) {
            return $this->addError('some_field', 'some error where we already have a matching eckid');
        }

        $user->eckId = Session::get('saml_attributes')['eckId'][0];
        $user->save();
        $user->redirectToCakeWithTemporaryLogin();
    }
}
