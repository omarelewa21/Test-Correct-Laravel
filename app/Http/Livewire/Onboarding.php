<?php

namespace tcCore\Http\Livewire;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\DemoTeacherRegistration;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Requests\Request;
use tcCore\SamlMessage;
use tcCore\SchoolLocation;
use tcCore\Shortcode;
use tcCore\ShortcodeClick;
use tcCore\TemporaryLogin;
use tcCore\User;

class Onboarding extends TCComponent
{

    protected $allowedLevels = ['MBO','HO','PO','VO'];
    public $registration;
    public $email;
    public $password;
    public $password_confirmation;
    public $ref;
    public $invited_by;
    public $step = 1;
    public $level;

    public $btnDisabled = true;
    public $confirmed;
    public $shouldDisplayEmail = false;
    public $resendVerificationMail = false;
    public $newRegistration = false;

    public $warningStepOne = false;
    public $warningStepTwo = false;

    public $warningStepOneConfirmed = false;
    public $warningStepTwoConfirmed = false;
    public $subjectOptions = '';
    public $selectedSubjects = [];
    public $selectedSubjectsString = '';
    public $domain = '';

    public $entree_message = '';

    public $showSubjects = true;


    protected $queryString = ['step', 'email', 'confirmed', 'ref','entree_message', 'level'];
    /**
     * @var true
     */
    public bool $setFocusOnError = false;

    protected function messages(){
        return [
            'registration.name_first.required'      => __('registration.name_first_required'),
            'registration.name.required'            => __('registration.name_last_required'),
            'registration.gender.required'          => __('registration.gender_required'),
            'password.required'                     => __('registration.password_required'),
            'password.min'                          => __('registration.password_min'),
            'password.same'                         => __('registration.password_same'),
            'registration.school_location.required' => __('registration.school_location_required'),
            'registration.website_url.required'     => __('registration.website_url_required'),
            'registration.address.required'         => __('registration.address_required'),
            'registration.house_number.required'    => __('registration.house_number_required'),
            'registration.house_number.regex'       => __('registration.house_number_regex'),
            'registration.postcode.required'        => __('registration.postcode_required'),
            'registration.postcode.min'             => __('registration.postcode_min'),
            'registration.postcode.regex'           => __('registration.postcode_regex'),
            'registration.city.required'            => __('registration.city_required'),
            'registration.username.required'        => __('registration.username_required'),
            'registration.username.email'           => __('registration.username_email'),
            'registration.username.unique'     => __('registration.username_unique'),
            'domain.required'                       => __('registration.domain_required'),
        ];
    }

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
            'registration.username'                     => 'required|email:rfc,dns|unique:users,username',
            'registration.name_first'                   => 'sometimes',
            'registration.name'                         => 'sometimes',
            'registration.name_suffix'                  => 'sometimes',
            'registration.registration_email_confirmed' => 'sometimes',
            'registration.invitee'                      => 'sometimes',
            'password'                                  => 'sometimes',
            'registration.subjects'                     => 'sometimes',
            'domain'                                    => 'sometimes',
        ];

        if ($this->step === 1) {
            $extra1 = [];
            if($this->useDomainInsteadOfSubjects()){
                $extra1 = ['domain' => 'required'];
            }
            return array_merge($default, [
                'registration.gender'           => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first'       => 'required|string',
                'registration.name'             => 'required|string',
                'registration.name_suffix'      => 'sometimes',
                'password'                      => 'required|same:password_confirmation|'. User::getPasswordLengthRule(),
            ], $extra1);
        }

        return $default;
    }

    public function rulesStep2()
    {
        return [
            'registration.school_location' => 'required',
            'registration.website_url'     => '',
            'registration.address'         => 'required',
            'registration.house_number'    => 'required|regex:/\d/',
            'registration.postcode'        => 'required|min:6|regex:/^[1-9][0-9]{3}\s?[a-zA-Z]{2}$/',
            'registration.city'            => 'required',
        ];
    }

    public function hasNoSubjects()
    {
        return $this->level === "PO";
    }

    public function useDomainInsteadOfSubjects()
    {
        return $this->level === "MBO" || $this->level === "HO";
    }

    public function mount()
    {
        $this->registration = new DemoTeacherRegistration;
        $this->registration->username = $this->email;
        $this->registration->gender = 'male';

        if (!$this->step != 1 || $this->step > '4') {
            $this->step = 1;
        }
        if (!$this->email) {
            $this->email = '';
        }

        if($this->level){
            $this->level = Str::upper($this->level);
        }

        $this->setCorrectLevelToRegistration();

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

        if(request()->has('registerId')){
            $samlMessage = SamlMessage::getSamlMessageIfValid(request()->get('registerId'));
            if($samlMessage){
                $data = $samlMessage->data;
                if($data) {
                    collect([['username', 'emailAddress'], ['name', 'lastName'], ['name_suffix', 'nameSuffix'],['name_first', 'firstName']])->eachSpread(function ($registrationKey, $entreeKey) use ($data) {
                        if (property_exists($data, $entreeKey)) {
                            $this->registration->$registrationKey = $data->$entreeKey;
                        }
                    });
                }
            }
        }

        $this->registration->registration_email_confirmed = $this->confirmed;
        $this->setSubjectOptions();
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
        if(!$this->useDomainInsteadOfSubjects()) {
            $this->setSelectedSubjectsString();
            $this->setSubjectOptions();
        }
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

    public function step1()
    {
        $this->setFocusOnError = true;
        $this->validate();
        if (!$this->checkInputForLength() && !$this->warningStepOneConfirmed) {
            $this->warningStepOneConfirmed = true;
            return;
        }
        if ($this->ref != null && $this->isInvitedBySameDomain($this->registration->username)) {
            $inviter = User::find($this->registration->invitee);
            $schoolInfo = SchoolLocation::find($inviter->school_location_id);
            $this->fillSchoolData($schoolInfo);
        } else {
            $this->clearSchoolData();
        }
        $this->step = 2;
//        $this->btnStepTwoDisabledCheck();
        $this->warningStepOneConfirmed = false;
    }

    public function step2()
    {
        $this->setFocusOnError = true;
        $this->validate();
        if (!$this->checkInputForLength() && !$this->warningStepTwoConfirmed) {
            $this->warningStepTwoConfirmed = true;
            return;
        }
        $this->validate($this->rulesStep2());
        if($this->hasNoSubjects()){
            $this->registration->subjects = sprintf("%s: so no subjects",$this->level);
        }
        else if($this->useDomainInsteadOfSubjects()){
            $this->registration->subjects = sprintf("%s:%s",$this->level,$this->domain);
        }

        $this->registration->save();
        try {
            $this->newRegistration = $this->registration->addUserToRegistration($this->password, $this->registration->invitee, $this->ref);
            $this->step = 3;
        } catch (\Throwable $e) {
            $this->step = 'error';
            Bugsnag::notifyException($e);
        }
    }

    protected function setCorrectLevelToRegistration()
    {
        $this->registration->level = ($this->level && in_array($this->level,$this->allowedLevels)) ? $this->level : "VO";
    }

    public function loginUser()
    {
        $redirectUrl = BaseHelper::getLoginUrl();
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

    public function fillSchoolData(SchoolLocation $schoolInfo)
    {

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
        $this->setFocusOnError = false;
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

    public function syncSelectedSubjects($subjects)
    {
        $this->registration->subjects = implode(';',$subjects);
        $this->selectedSubjects = $subjects;
    }

    protected function setSubjectOptions()
    {
        $subjects = BaseSubject::where('show_in_onboarding',true)->forLevel($this->registration->level)->get()->pluck('name')->toArray();
        $subjects = array_unique($subjects);
        sort($subjects);
//        $subjects = $this->translateSubjects($subjects);
        $subjects = array_diff($subjects,$this->selectedSubjects);
        $this->subjectOptions = json_encode($subjects,JSON_HEX_APOS);
    }

    protected function setSelectedSubjectsString()
    {
        $this->selectedSubjectsString =  json_encode($this->selectedSubjects,JSON_HEX_APOS);
    }

    private function translateSubjects($subjects)
    {
        return collect($subjects)->map(function($subject) {
            return __('subject.'.$subject);
        })->toArray();
    }

    public function finish()
    {
        $this->step = 4;
    }
}
