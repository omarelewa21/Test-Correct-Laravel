<?php

namespace tcCore\Http\Livewire\Auth;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\FailedLogin;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Jobs\SendForgotPasswordMail;
use tcCore\SamlMessage;
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

    protected $queryString = ['tab', 'uuid', 'entree_error_message'];

    public $tab = 'login';

    public $uuid = '';

    public $entree_error_message = '';

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
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function login()
    {
        $this->resetErrorBag();
        $this->entree_error_message = '';

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

        if(EntreeHelper::shouldPromptForEntree(auth()->user())) {
            auth()->logout();
            return $this->addError('should_first_go_to_entree', __('auth.should_first_login_using_entree'));
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
        $this->entree_error_message = '';
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

    private function couldBeEmail($email): bool
    {
        return Str::of($email)->containsAll(['@', '.']);
    }

    public function sendForgotPasswordEmail()
    {
        $this->entree_error_message = '';
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
        $this->entree_error_message = '';
        $credentials = [
            'username' => $this->entreeEmail,
            'password' => $this->entreePassword,
        ];


        $message = SamlMessage::whereUuid($this->uuid)->first();

        if ($message == null) {
            return $this->addError('entree_error', __('auth.no_saml_message_found'));
        }

        if($message->created_at < Carbon::now()->subMinutes(5)->toDateTimeString()) {
            return $this->addError('entree_error', __('auth.saml_message_to_old'));
        }

        if (!auth()->attempt($credentials)) {
            $this->createFailedLogin();
            return $this->addError('entree_error', __('auth.incorrect_credentials'));
        }
        $user = auth::user();

        if (! empty($user->eckId)) {
            return $this->addError('entree_error', __('auth.eck_id_already_set_for_user'));
        }

        $user->eckId = Crypt::decryptString($message->eck_id);
        $user->username = $message->email;
        $message->delete();
        $user->save();
        $user->redirectToCakeWithTemporaryLogin();
    }
}
