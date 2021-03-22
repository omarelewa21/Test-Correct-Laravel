<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\DemoTeacherRegistration;
use tcCore\Http\Requests\Request;
use tcCore\SchoolLocation;
use tcCore\Shortcode;
use tcCore\ShortcodeClick;
use tcCore\TemporaryLogin;
use tcCore\User;

class Onboarding extends Component
{
    public $registration;
    public $email;
    public $password;
    public $password_confirmation;
    public $ref;
    public $invited_by;
    public $step = 1;

    public $btnDisabled = true;
    public $confirmed;
    public $shouldDisplayEmail = false;
    public $resendVerificationMail = false;
    public $newRegistration = false;

    public $warningStepOne = false;
    public $warningStepTwo = false;

    public $warningStepOneConfirmed = false;
    public $warningStepTwoConfirmed = false;

    protected $queryString = ['step', 'email', 'confirmed', 'ref'];

    protected $messages = [
        'registration.name_first.required'      => 'Voornaam is verplicht',
        'registration.name.required'            => 'Achternaam is verplicht',
        'registration.gender.required'          => 'Geef uw geslacht op',
        'password.required'                     => 'Wachtwoord is verplicht',
        'password.min'                          => 'Wachtwoord moet langer zijn dan 8 karakters',
        'password.regex'                        => 'Wachtwoord voldoet niet aan de eisen',
        'password.same'                         => 'Wachtwoord komt niet overeen',
        'registration.school_location.required' => 'Schoolnaam is verplicht',
        'registration.website_url.required'     => 'Website is verplicht',
        'registration.address.required'         => 'Adres is verplicht',
        'registration.house_number.required'    => 'Huisnummer is verplicht',
        'registration.house_number.regex'       => 'Huisnummer bevat geen nummer',
        'registration.postcode.required'        => 'Postcode is verplicht',
        'registration.postcode.min'             => 'Postcode is niet geldig',
        'registration.postcode.regex'           => 'Postcode is niet geldig',
        'registration.city.required'            => 'Plaatsnaam is verplicht',
        'registration.username.required'        => 'E-mailadres is verplicht',
        'registration.username.email'           => 'E-mailadres is niet geldig',
    ];

    public function rules()
    {
        $default = [
            'registration.school_location'              => 'sometimes',
            'registration.website_url'                  => 'sometimes',
            'registration.address'                      => 'sometimes',
            'registration.house_number'                 => 'sometimes',
            'registration.postcode'                     => 'sometimes',
            'registration.city'                         => 'sometimes',
            'registration.gender'                       => 'sometimes',
            'registration.gender_different'             => 'sometimes',
            'registration.name_first'                   => 'sometimes',
            'registration.username'                     => 'required|email:rfc,dns',
            'registration.name'                         => 'sometimes',
            'registration.name_suffix'                  => 'sometimes',
            'registration.registration_email_confirmed' => 'sometimes',
            'registration.invitee'                      => 'sometimes',
            'password'                                  => 'sometimes',
        ];

        if ($this->step === 1) {
            return array_merge($default, [
                'registration.gender'           => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first'       => 'required|string',
                'registration.name'             => 'required|string',
                'registration.name_suffix'      => 'sometimes',
                'password'                      => 'required|min:8|regex:/\d/|regex:/[^a-zA-Z\d]/|same:password_confirmation',
            ]);
        }

        return $default;
    }

    public function rulesStep2()
    {
        return [
            'registration.school_location' => 'required',
            'registration.website_url'     => 'required',
            'registration.address'         => 'required',
            'registration.house_number'    => 'required|regex:/\d/',
            'registration.postcode'        => 'required|min:6|regex:/^[1-9][0-9]{3}\s?[a-zA-Z]{2}$/',
            'registration.city'            => 'required',
        ];
    }

    public function mount()
    {
        $this->registration = new DemoTeacherRegistration;
        $this->registration->username = $this->email;
        $this->registration->gender = 'male';

        if (!$this->step != 1 || $this->step >= '4') {
            $this->step = 1;
        }
        if (!$this->email) {
            $this->email = '';
        }
        if ($this->isUserConfirmedWithEmail()) {
            $this->confirmed = 0;
            $this->shouldDisplayEmail = true;
        }
        if ($this->ref && Uuid::isValid($this->ref)) {
            $shortcodeId = ShortcodeClick::whereUuid($this->ref)->first();
            if (null !== $shortcodeId) {
                $invited_by = Shortcode::where('id', $shortcodeId->shortcode_id)->first();
                $this->registration->invitee = $invited_by->user_id;
            }
        }

        $this->registration->registration_email_confirmed = $this->confirmed;
    }

    private function isUserConfirmedWithEmail()
    {
        return (!$this->confirmed) || ($this->confirmed === 1 && !$this->email);
    }

    public function backToStepOne()
    {
        $this->step = 1;
//        $this->btnStepOneDisabledCheck();
    }

    public function render()
    {
        return view('livewire.onboarding')->layout('layouts.onboarding');
    }

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

    public function step1()
    {
        $this->validate();
        if (!$this->checkInputForLength() && !$this->warningStepOneConfirmed) {
            $this->warningStepOneConfirmed = true;
            return;
        }
        if ($this->ref != null && $this->isInvitedBySameDomain($this->registration->username)) {
            $this->fillSchoolData($this->registration->invitee);
        } else {
            $this->clearSchoolData();
        }
        $this->step = 2;
//        $this->btnStepTwoDisabledCheck();
        $this->warningStepOneConfirmed = false;
    }

    public function step2()
    {
        $this->validate();
        if (!$this->checkInputForLength() && !$this->warningStepTwoConfirmed) {
            $this->warningStepTwoConfirmed = true;
            return;
        }
        $this->validate($this->rulesStep2());
        $this->registration->save();
        try {
            $this->newRegistration = $this->registration->addUserToRegistration($this->password, $this->registration->invitee, $this->ref);
            $this->step = 3;
        } catch (\Throwable $e) {
            $this->step = 'error';
        }
    }

    public function loginUser()
    {
        $redirectUrl = config('app.url_login');
        if ($this->newRegistration) {
            $user = User::where('username', $this->registration->username)->first();
            if ($user) {
                $temporaryLogin = TemporaryLogin::create(
                    ['user_id' => $user->getKey()]
                );
                $redirectUrl = $temporaryLogin->createCakeUrl();
            }
        }
        Redirect::to($redirectUrl);
    }

    public function resendEmailVerificationMail()
    {
        $user = User::where('username', $this->registration->username)->first();
        if ($user) {
            if ($user->account_verified === null) {
                $user->resendEmailVerificationMail();
                $this->resendVerificationMail = true;
            }
        }
    }

    private function btnStepOneDisabledCheck()
    {
        if ($this->step == 1) {
            $this->btnDisabled = (
                empty($this->registration->name_first)
                || empty($this->registration->gender)
                || empty($this->registration->name)
                || empty($this->password_confirmation)
                || empty($this->password)
                || empty($this->registration->username)
            );
            if ($this->confirmed != 1 && !$this->btnDisabled) {
                $this->btnDisabled = empty($this->registration->username);
            }
        }

    }

    private function btnStepTwoDisabledCheck()
    {
        if ($this->step == 2) {
            $this->btnDisabled = (
                empty($this->registration->city)
                || empty($this->registration->school_location)
                || empty($this->registration->website_url)
                || empty($this->registration->address)
                || empty($this->registration->postcode)
                || empty($this->registration->house_number)
            );
        }
    }

    public function checkInputForLength()
    {
        if ($this->step == 1) {
            if (strlen($this->registration->name_first) <= 1
                || strlen($this->registration->name) <= 1) {
                $this->warningStepOne = true;
                return false;
            }

            $this->warningStepOne = false;
            return true;

        }
        if ($this->step == 2) {
            if (strlen($this->registration->city) <= 1
                || strlen($this->registration->school_location) <= 1
                || strlen($this->registration->website_url) <= 1
                || strlen($this->registration->address) <= 1) {
                $this->warningStepTwo = true;
                return false;
            }
            $this->warningStepTwo = false;
            return true;
        }
    }

    public function isInvitedBySameDomain($username)
    {
        $inviter = User::find($this->registration->invitee);
        $inviterDomain = explode('@', $inviter->username)[1];

        return $inviterDomain === explode('@', $username)[1];
    }

    public function fillSchoolData($inviter)
    {
        $inviter = User::find($inviter);
        $schoolInfo = SchoolLocation::find($inviter->school_location_id);
        $this->registration->school_location = $schoolInfo->name;
        $this->registration->address = $schoolInfo->visit_address;
        $this->registration->postcode = $schoolInfo->visit_postal;
        $this->registration->house_number = filter_var($schoolInfo->visit_address, FILTER_SANITIZE_NUMBER_INT);
        $this->registration->city = $schoolInfo->visit_city;
    }

    public function clearSchoolData()
    {
        $this->registration->school_location = null;
        $this->registration->address = null;
        $this->registration->postcode = null;
        $this->registration->house_number = null;
        $this->registration->city = null;
    }

    public function updating(&$name, &$value)
    {
        Request::filter($value);
    }

    public function updated($propertyName)
    {
        $this->btnDisabled = false;
//
//        $this->btnStepOneDisabledCheck();
//        $this->btnStepTwoDisabledCheck();

        if ($propertyName === 'password_confirmation') {
            $propertyName = 'password';
        }

        if ($this->registration->gender != 'different') {
            $this->registration->gender_different = '';
        }

        if ($propertyName != 'password') {
            $this->validateOnly($propertyName);
        }
    }


}
