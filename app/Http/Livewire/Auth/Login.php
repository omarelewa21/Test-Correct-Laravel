<?php

namespace tcCore\Http\Livewire\Auth;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\FailedLogin;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Helpers\TestTakeCodeHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Requests\Request;
use tcCore\Jobs\SendForgotPasswordMail;
use tcCore\Rules\EmailDns;
use tcCore\Rules\EmailImproved;
use tcCore\Rules\NistPasswordRules;
use tcCore\Rules\TrueFalseRule;
use tcCore\SamlMessage;
use tcCore\Services\EmailValidatorService;
use tcCore\TestKind;
use tcCore\User;
use tcCore\TestTake;
use tcCore\TestTakeCode;

class Login extends TCComponent
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
    public $guest_message_shown = 0;

    protected $queryString = [
        'tab'                  => ['except' => 'login'],
        'active_overlay'       => ['except' => ''],
        'login_tab'            => ['except' => 1],
        'uuid'                 => ['except' => ''],
        'entree_error_message' => ['except' => ''],
        'fatal_error_message'  => ['except' => false],
        'block_back'           => ['except' => false],
        'guest_message'        => ['except' => ''],
        'guest_message_type'   => ['except' => ''],
        'device'               => ['except' => '']
    ];

    public $tab = 'login';
    public $device = '';

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
    public $active_overlay = '';
//    public $entreeTab = false;

    public $showTestCode = false;
    public $forgotPasswordButtonDisabled = true;
    public $connectEntreeButtonDisabled = true;

    private $xssPropsToClean = [
        'firstName',
        'suffix',
        'lastName',
    ];

    public $showAuthModal = false;
    public $authModalRoleType;
    public $showSendForgotPasswordNotification = false;

    public $schoolLocation;

    public $take = null;

    public $studentDownloadUrl = 'https://www.test-correct.nl/student/';

    public $errorKeys = [];
    protected $preventFieldTransformation = ['password'];
    protected $listeners = ['open-auth-modal' => 'openAuthModal', 'password_reset' => 'passwordReset'];

    protected function getRules()
    {
        return [
            'username' => ['required', new EmailImproved],
            'password' => 'required',
        ];
    }

    public function getCustomValidator(): Login|m
    {
        return $this->withValidator(function (\Illuminate\Validation\Validator $validator) {
            $validator->after(function ($validator) {
                $this->errorKeys = array_keys($validator->failed());
            });
        });
    }

    protected function messages(): array
    {
        return [
            'password.required' => __('auth.password_required'),
            'username.required' => __('auth.email_required'),
            'username.email'    => __('auth.email_incorrect'),
            'username.EmailImproved'    => __('auth.email_incorrect') . 2,
        ];
    }


    public function mount()
    {
        $this->handleDirectLinkOnEnter();

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

        $this->username = trim($this->username);

        $credentials = $this->getCustomValidator()->validate();

        if (!auth()->attempt($credentials)) {
            if ($this->requireCaptcha) {
                $this->reset('captcha');
                $this->dispatchBrowserEvent('refresh-captcha', ['src' => captcha_src()]);
                return;
            }
            $this->createFailedLogin();
            $this->addError('invalid_user', __('auth.failed'));
            $this->errorKeys = ['invalid_user'];
            return;
        }

        if ((Auth()->user()->isA('teacher') || Auth()->user()->isA('student')) && EntreeHelper::shouldPromptForEntree(auth()->user())) {
            auth()->logout();
            $this->errorKeys = ['should_first_go_to_entree'];
            return $this->addError('should_first_go_to_entree', __('auth.should_first_login_using_entree'));
        }

        AppVersionDetector::handleHeaderCheck();
        $this->doLoginProcedure();

        if ($this->checkIfShouldRedirectToTestTake()) {
            return;
        };

        $user = auth()->user();
        $route = $user->getTemporaryCakeLoginUrl();
        if ($user->isA('Student') && $user->schoolLocation->allow_new_student_environment) {
            $route = route('student.dashboard');
        }
// Als dit ooit weer aangezet wordt, vergeet de redirect niet aan te passen naar zoiets als hierboven
//        if ($user->isA('Account manager')) {
//            return redirect()->intended(route('uwlr.grid'));
//        }

        $expiration_date = $user->password_expiration_date;
        if ($expiration_date && $expiration_date->lessThan(Carbon::now())) {
            return $this->emit('openModal', 'force-password-change-modal');
        }
        return $this->redirect($route);
    }

    public function guestLogin()
    {
        if (!$this->filledInNecessaryGuestInformation()) {
            return false;
        }

        if (!$this->isTestTakeCodeCorrectFormat()) {
            $this->errorKeys = ['invalid_test_code'];
            return $this->addError('invalid_test_code', __('auth.test_code_invalid'));
        }

        $testCodeHelper = new TestTakeCodeHelper();

        $testTakeCode = $testCodeHelper->getTestTakeCodeIfExists($this->testTakeCode);
        if (!$testTakeCode
            || !$testTakeCode->testTake
            || !$testTakeCode->testTake->test
            || ( // fail if not an assignment and doesn't start today
                $testTakeCode->testTake->test->test_kind_id !== TestKind::ASSIGNMENT_TYPE
                && $testTakeCode->testTake->time_start != Carbon::today()
            )
            || ( // fail if it is an assignment, and the time_start is later than today (starts later than today) or the time_end smaller than today (ends earlier than today)
                $testTakeCode->testTake->test->test_kind_id == TestKind::ASSIGNMENT_TYPE
                && (
                    $testTakeCode->testTake->time_start > Carbon::today()
                    || $testTakeCode->testTake->time_end < Carbon::today()
                )
            )
        ) {
            $this->errorKeys[] = 'no_test_found_with_code';
            return $this->addError('no_test_found_with_code', __('auth.no_test_found_with_code'));
        }

        if (!$testTakeCode->testTake->guest_accounts) {
            $this->errorKeys[] = 'guest_account_not_allowed';
            return $this->addError('guest_account_not_allowed', __('auth.guest_account_not_allowed'));
        }

        AppVersionDetector::handleHeaderCheck();

        $error = $testCodeHelper->handleGuestLogin($this->gatherGuestData(), $testTakeCode);
        if (!empty($error)) {
            $this->errorKeys[] = $error[0];
            return $this->addError($error[0], $error[0]);
        }
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.base');
    }

    public function goToPasswordReset()
    {
        $this->entree_error_message = '';
        $this->redirect(route('password.reset'));
    }

    private function doLoginProcedure()
    {
        BaseHelper::doLoginProcedure();
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

        if (!captcha_check($this->captcha)) {
            $this->reset('captcha');
            $this->addError('captcha', __('auth.incorrect_captcha'));
            $this->dispatchBrowserEvent('refresh-captcha', ['src' => captcha_src()]);
            return false;
        }

//        $rulesWithCaptcha = array_merge($this->rules, ['captcha' => 'required|captcha']);
        $this->validate();
        return true;
    }

    public function updated($name, $value)
    {
        switch ($name) {
            case 'firstName':
                $this->resetErrorBag();
                $this->validateGuestFirstName();
                break;
            case 'lastName':
                $this->validateGuestLastName();
                break;
            case 'username':
                $this->resetErrorBag();
            default:
                $this->validateOnly($name);
        }


        $this->couldBeEmail($this->forgotPasswordEmail) ? $this->forgotPasswordButtonDisabled = false : $this->forgotPasswordButtonDisabled = true;

        if ($this->couldBeEmail($this->entreeEmail) && filled($this->entreePassword)) {
            $this->connectEntreeButtonDisabled = false;
        }

    }

    private function cleanXss($name, $value)
    {
        if (in_array($name, $this->xssPropsToClean)) {
            return clean($value);
        }
        return $value;
    }


    public function updatedShowTestCode($value)
    {
        if (!$value) {
            $this->testTakeCode = [];
        }
    }

    private function couldBeEmail($email): bool
    {
        return validator(
            ['email' => $email],
            ['email' => ['required', new EmailImproved]]
        )->passes();
    }

    public function sendForgotPasswordEmail()
    {
        $this->active_overlay = '';
        $this->login_tab = 1;
        $this->entree_error_message = '';
        $user = User::whereUsername($this->forgotPasswordEmail)->first();
        if ($user) {
            $token = Password::getRepository()->create($user);
            $url = sprintf('%slogin/?active_overlay=reset_password&token=%%s', config('app.base_url'));
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
        $validValues = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        foreach ($this->testTakeCode as $key => $value) {
            if(! in_array($value, $validValues)) {
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
                $this->dispatchBrowserEvent('refresh-captcha', ['src' => captcha_src()]);
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
        $this->rules['username'] = $this->getSchoolLocationAcceptedEmailDomainRule();

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

    private function getSchoolLocationAcceptedEmailDomainRule()
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
        $this->errorKeys = [];
        $this->validateGuestFirstName($hasNoError, true);
        $this->validateGuestLastName($hasNoError, true);

        return $hasNoError;
    }

    private function validateGuestFirstName(&$hasNoError = true, $focusOnInputWithError = false)
    {
        if (blank($this->firstName)) {
            if ($focusOnInputWithError) {
                array_unshift($this->errorKeys, 'empty_guest_first_name');
            }
            $this->addError('empty_guest_first_name', __('auth.empty_guest_first_name'));
            $hasNoError = false;
        }
    }

    private function validateGuestLastName(&$hasNoError = true, $focusOnInputWithError = false)
    {
        if (blank($this->lastName)) {
            if ($focusOnInputWithError) {
                $this->errorKeys[] = 'empty_guest_last_name';
            }
            $this->addError('empty_guest_last_name', __('auth.empty_guest_last_name'));

            $hasNoError = false;
        }
    }

    public function updating(&$name, &$value)
    {
        if (in_array($name, $this->xssPropsToClean)) {
            $value = BaseHelper::returnOnlyRegularAlphaNumeric($value,'');
            Request::filter($value);

        }
    }

    private function gatherGuestData()
    {
        return [
            'name_first'  => (trim($this->firstName)),
            'name_suffix' => (trim($this->suffix)),
            'name'        => (trim($this->lastName))
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
        $this->resetErrorBag();
    }

    private function clearGuestMessages()
    {
        $this->guest_message = '';
        $this->guest_message_type = '';
        $this->showGuestError = false;
        $this->showGuestSuccess = false;
    }

    public function dispatchGuestSuccessNotification()
    {
        if ($this->showGuestSuccess) {
            $this->dispatchBrowserEvent('notify',
                [
                    'type'    => 'guest_success',
                    'title'   => __('auth.' . $this->guest_message),
                    'message' => __('auth.' . $this->guest_message . '_sub'),
                ]
            );
        }
    }

    public function passwordReset()
    {
        $this->active_overlay = '';

        $this->dispatchBrowserEvent('notify',
            [
                'type'    => 'guest_success',
                'title'   => __('passwords.reset_title'),
                'message' => __('passwords.reset'),
            ]
        );
    }

    private function checkIfShouldRedirectToTestTake()
    {
        if ($this->take) {
            return redirect()->route('take.directLink', ['testTakeUuid' => $this->take]);
        }

        if ($this->isTestTakeCodeCorrectFormat()) {
            $code = implode('', $this->testTakeCode);
            $testTakeCode = TestTakeCode::where('code', $code)->with('testTake')->first();
            if (is_null($testTakeCode)) {
                return false;
            }
            return redirect()->route('take.directLink', ['testTakeUuid' => $testTakeCode->testTake->uuid]);
        }
        return false;
    }

    private function handleDirectLinkOnEnter(): void
    {
        $directLink = request()->get('directlink');
        if (!$directLink || !Uuid::isValid($directLink)) return;

        $take = TestTake::whereUuid($directLink)->with('testTakeCode')->first();

        if (!$take) return;

        if ($take->testTakeCode) {
            $this->testTakeCode = str_split($take->testTakeCode->code);
        }

        $this->take = $take->uuid;
    }
}
