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
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Helpers\TestTakeCodeHelper;
use tcCore\Jobs\SendForgotPasswordMail;
use tcCore\SamlMessage;
use tcCore\Services\EmailValidatorService;
use tcCore\User;

class Login extends Component
{
    public $doIHaveATcAccount = 1;
    public $doIHaveATcAccountChoice = null;

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

    protected $queryString = [
        'tab'                  => ['except' => 'login'],
        'login_tab'            => ['except' => 1],
        'uuid'                 => ['except' => ''],
        'entree_error_message' => ['except' => ''],
        'fatal_error_message'  => ['except' => false],
        'block_back'           => ['except' => false],
        'guest_message'        => ['except' => ''],
        'guest_message_type'        => ['except' => ''],
    ];

    public $tab = 'login';

    public $login_tab = 1;

    public $uuid = '';

    public $fatal_error_message = false;

    public $block_back = false;

    public $entree_error_message = '';
    public $guest_message = '';
    public $guest_message_type = '';
    public $showGuestError = false;
    public $showGuestSuccess = false;

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

    public $schoolLocation;

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

        $this->handleLoginTabScenarios();
    }

    public function login()
    {
        $this->resetErrorBag();
        $this->entree_error_message = '';

        if (!$this->captcha && FailedLogin::doWeNeedExtraSecurityLayer($this->username)) {
            $this->requireCaptcha = true;
            return;
        }
        if ($this->captcha && !$this->validateCaptcha()) {
            return;
        }

        $credentials = $this->validate();
        if (!auth()->attempt($credentials)) {
            if ($this->requireCaptcha) {
                $this->reset('captcha');
                $this->emit('refresh-captcha');
                return;
            }
            $this->createFailedLogin();
            $this->addError('invalid_user', __('auth.failed'));
            return;
        }

        if ((Auth()->user()->isA('teacher') || Auth()->user()->isA('student')) && EntreeHelper::shouldPromptForEntree(auth()->user())) {
            auth()->logout();
            return $this->addError('should_first_go_to_entree', __('auth.should_first_login_using_entree'));
        }

        $this->doLoginProcedure();

        AppVersionDetector::handleHeaderCheck();

        $user = auth()->user();
        if ($user->isA('Student') && $user->schoolLocation->allow_guest_accounts) {
            return redirect()->intended(route('student.dashboard'));
        }
        if ($user->isA('Account manager')) {
            return redirect()->intended(route('uwlr.grid'));
        }

        auth()->user()->redirectToCakeWithTemporaryLogin();
    }

    public function guestLogin()
    {
        if (!$this->filledInNecessaryGuestInformation()) {
            return false;
        }

        if (!$this->isTestTakeCodeCorrectFormat()) {
            return $this->addError('invalid_test_code', __('auth.test_code_invalid'));
        }

        $testCodeHelper = new TestTakeCodeHelper();

        $testTakeCode = $testCodeHelper->getTestTakeCodeIfExists($this->testTakeCode);
        if (!$testTakeCode) {
            return $this->addError('no_test_found_with_code', __('auth.no_test_found_with_code'));
        }

        AppVersionDetector::handleHeaderCheck();

        $error = $testCodeHelper->handleGuestLogin($this->gatherGuestData(), $testTakeCode);
        if (!empty($error)) {
            return $this->addError($error[0], $error[0]);
        }
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
        auth()->user()->setSessionHash($sessionHash);
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

        if ($this->couldBeEmail($this->entreeEmail) && filled($this->entreePassword)) {
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
            $url = sprintf('%spassword-reset/?token=%%s', config('app.base_url'));
            $urlLogin = route('auth.login');

            try {
                Mail::to($user->username)->send(new SendForgotPasswordMail($user, $token, $url, $urlLogin));
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
            }
        }

        $this->showSendForgotPasswordNotification = true;
    }

    private function isTestTakeCodeCorrectFormat(): bool
    {
        if (count($this->testTakeCode) != 6) {
            return false;
        }

        foreach ($this->testTakeCode as $key => $value) {
            $value = (int)$value;
            if ($value === null || !is_int($value) || Str::length($value) != 1) {
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

    public function samlMessageValid()
    {
        $message = SamlMessage::whereUuid($this->uuid)->first();
        if ($message == null) {
            return false;
        }

        if ($message->created_at < Carbon::now()->subMinutes(5)->toDateTimeString()) {
            return false;
        }

        return true;
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

        if ($message->created_at < Carbon::now()->subMinutes(5)->toDateTimeString()) {
            return $this->addError('entree_error', __('auth.saml_message_to_old'));
        }

        if (!auth()->attempt($credentials)) {
            $this->createFailedLogin();
            return $this->addError('entree_error', __('auth.incorrect_credentials'));
        }
        $user = auth::user();

        if (!empty($user->eckId)) {
            return $this->addError('entree_error', __('auth.eck_id_already_set_for_user'));
        }

        $user->eckId = Crypt::decryptString($message->eck_id);
        $user->username = $message->email;
        $message->delete();
        $user->save();
        $user->redirectToCakeWithTemporaryLogin();
    }

    public function loginForNoMailPresent()
    {
        $message = SamlMessage::whereUuid($this->uuid)->first();

        if ($message == null) {
            return $this->addError('entree_error', __('auth.no_saml_message_found'));
        }

        $this->resetErrorBag();
        $this->entree_error_message = '';

        if (!$this->captcha && FailedLogin::doWeNeedExtraSecurityLayer($this->username)) {
            $this->requireCaptcha = true;
            return;
        }
        if ($this->captcha && !$this->validateCaptcha()) {
            return;
        }

        $credentials = $this->validate();
        if (!auth()->attempt($credentials)) {
            if ($this->requireCaptcha) {
                $this->reset('captcha');
                $this->emit('refresh-captcha');
                return;
            }
            $this->createFailedLogin();
            $this->addError('invalid_user', __('auth.failed'));
            return;
        }
        $this->doLoginProcedure();

        if (EntreeHelper::initWithMessage($message)->setContext('livewire')->tryAccountMatchingWhenNoMailAttributePresent(auth()->user()) === true) {
            return redirect(route('entree-link', ['linked' => auth()->user()->uuid, 'with_account' => true]));
//            auth()->user()->redirectToCakeWithTemporaryLogin();
        }
    }

    public function emailEnteredForNoMailPresent()
    {
        // validate entered emailaddress
        $this->rules['username'] = $this->getSchoolLocationAccptedEmailDomainRule();

        $this->validateOnly('username');
        if (User::where('username', $this->username)->exists()) {
            return $this->addError('username', __('auth.email_already_in_use'));
        }
        $message = SamlMessage::whereUuid($this->uuid)->first();

        if ($message == null) {
            return $this->addError('entree_error', __('auth.no_saml_message_found'));
        }


        if ($user = EntreeHelper::handleNewEmailForUserWithoutEmailAttribute($message, $this->username)) {
            auth()->login($user);
            $this->doLoginProcedure();

            return redirect(route('entree-link', ['linked' => $user->uuid, 'with_account' => false]));
//            return $user->redirectToCakeWithTemporaryLogin();
        }

        return redirect()->to(
            route('auth.login',
                [
                    'tab'                 => 'fatalError',
                    'fatal_error_message' => 'auth.error_please_contact_service_desk',
                ]
            )
        );


    }

    public function noEntreeEmailNextStep()
    {
        $this->doIHaveATcAccount = $this->doIHaveATcAccountChoice;
    }

    public function backToNoEmailChoice()
    {
        $this->doIHaveATcAccountChoice = null;
        $this->doIHaveATcAccount = 1;
        $this->resetErrorBag();
    }

    public function returnToLogin()
    {

    }

    private function getSchoolLocationAccptedEmailDomainRule()
    {
        if ($this->uuid) {
            $eckId = optional(SamlMessage::whereUuid($this->uuid)->first())->eck_id;

            if ($eckId) {
                $user = User::findByEckId(Crypt::decryptString($eckId))->first();

                if (strlen($user->schoolLocation->accepted_mail_domain) > 0) {
                    $callback = function ($attribute, $value, $fail) use ($user) {
                        $validator = new EmailValidatorService(
                            $user->schoolLocation->accepted_mail_domain,
                            $value
                        );

                        if ($validator->passes() == false) {
                            $fail(
                                sprintf(
                                    'Het emailadres moet eindigen op: %s.',
                                    implode(' of ', $validator->getMessage())
                                )
                            );
                        }
                    };

                    return array_merge(
                        explode('|', $this->rules['username']),
                        [$callback]
                    );
                }
            }
        }

        return $this->rules['username'];
    }

    private function filledInNecessaryGuestInformation()
    {
        $hasNoError = true;
        if (blank($this->firstName)) {
            $this->addError('empty_guest_first_name', __('auth.empty_guest_first_name'));
            $hasNoError = false;
        }
        if (blank($this->lastName)) {
            $this->addError('empty_guest_last_name', __('auth.empty_guest_last_name'));
            $hasNoError = false;
        }

        return $hasNoError;
    }

    private function gatherGuestData()
    {
        return [
            'name_first'  => trim($this->firstName),
            'name_suffix' => trim($this->suffix),
            'name'        => trim($this->lastName)
        ];
    }

    private function handleLoginTabScenarios()
    {
        $availableLoginTabs = [1, 2];
        if (!in_array($this->login_tab, $availableLoginTabs)) {
            $this->login_tab = 1;
        }

        if (filled($this->guest_message_type) && filled($this->guest_message)) {
            $this->showGuestError = $this->guest_message_type == 'error';
            $this->showGuestSuccess = $this->guest_message_type == 'success';
        }
    }

    public function updatedLoginTab()
    {
        $this->clearGuestMessages();
    }

    private function clearGuestMessages()
    {
        $this->guest_message = '';
        $this->guest_message_type = '';
        $this->showGuestError = false;
        $this->showGuestSuccess = false;
    }
}
