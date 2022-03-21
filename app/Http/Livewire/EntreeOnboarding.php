<?php

namespace tcCore\Http\Livewire;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\DemoTeacherRegistration;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Requests\Request;
use tcCore\SchoolLocation;
use tcCore\Shortcode;
use tcCore\ShortcodeClick;
use tcCore\TemporaryLogin;
use tcCore\User;

class EntreeOnboarding extends Component
{
    protected $entreeData;
    public $registration;
    public $step = 1;

    public $btnDisabled = true;
    public $resendVerificationMail = false;
    public $newRegistration = false;

    public $warningStepOne = false;
    public $warningStepTwo = false;

    public $warningStepOneConfirmed = false;
    public $warningStepTwoConfirmed = false;
    public $subjectOptions = '';
    public $selectedSubjects = [];
    public $selectedSubjectsString = '';

    public $showSubjects = true;
    public $hasValidTUser = false;

    protected $queryString = ['step'];

    protected function messages(){
        return [
            'registration.name_first.required'      => __('registration.name_first_required'),
            'registration.name.required'            => __('registration.name_last_required'),
            'registration.gender.required'          => __('registration.gender_required'),
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
            'registration.name_first'                   => 'sometimes',
            'registration.username'                     => 'required|email:rfc,dns',
            'registration.name'                         => 'sometimes',
            'registration.name_suffix'                  => 'sometimes',
            'registration.registration_email_confirmed' => 'sometimes',
            'registration.subjects'                     => 'sometimes',
        ];

        if ($this->step === 1) {
            $rules = array_merge($default, [
                'registration.gender'           => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first'       => 'required|string',
                'registration.name'             => 'required|string',
                'registration.name_suffix'      => 'sometimes',
                'password'                      => 'required|same:password_confirmation|'. User::getPasswordLengthRule(),
            ]);
            if($this->hasValidTUser){
                foreach(['password','registration.password'] as $key){
                    unset($rules[$key]);
                }
            }
            return $rules;
        }

        return $default;
    }

    public function rulesStep2()
    {
        if(!$this->hasValidTUser) {
            return [
                'registration.school_location' => 'required',
                'registration.website_url' => 'required',
                'registration.address' => 'required',
                'registration.house_number' => 'required|regex:/\d/',
                'registration.postcode' => 'required|min:6|regex:/^[1-9][0-9]{3}\s?[a-zA-Z]{2}$/',
                'registration.city' => 'required',
            ];
        } else {
            return [];
        }
    }

    public function mount()
    {
        if(!$this->setEntreeDataFromSessionIfAvailable()){
            return true;
        }

        $this->registration = new DemoTeacherRegistration;

        if(property_exists($this->entreeData,'user')){
            if($this->entreeData->user->hasImportMailAddress()){
                $this->hasValidTUser = true;
                collect(['name_first','name_suffix','name','gender'])->each(function($key) {
                    $this->registration->$key = $this->entreeData->user->$key;
                });
                $this->registration->username = $this->entreeData->emailAddress;
                $this->registration->school_location = $this->entreeData->location->name;
                $this->showSubjects = false;
                $this->btnStepOneDisabledCheck();
            }
        }

//        $this->registration->username = $this->email;
//        $this->registration->gender = 'male';

        if (!$this->step != 1 || $this->step >= '4') {
            $this->step = 1;
        }

        $this->registration->registration_email_confirmed = $this->hasValidTUser;
        if(!$this->hasValidTUser) {
            $this->setSubjectOptions();
        }
    }

    protected function setEntreeDataFromSessionIfAvailable()
    {
        $this->entreeData = session('entreeData',false);
        if(!$this->entreeData){
            Redirect::to(route('onboarding.welcome'));
            return false;
        }
        return true;
    }

    public function backToStepOne()
    {
        $this->step = 1;
//        $this->btnStepOneDisabledCheck();
    }

    public function render()
    {
        $this->setSelectedSubjectsString();
        $this->setSubjectOptions();
        return view('livewire.entree-onboarding')->layout('layouts.onboarding');
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
        $this->validate();
        if (!$this->checkInputForLength() && !$this->warningStepOneConfirmed) {
            $this->warningStepOneConfirmed = true;
            return;
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
        if($this->hasValidTUser) {
            // we need to merge the data with the t user account
            $this->setEntreeDataFromSessionIfAvailable();
            $attr = [
              'mail' => [$this->entreeData->emailAddress],
              'eckId' => [Crypt::decryptString($this->entreeData->encryptedEckId)]
            ];
            return EntreeHelper::initAndHandleFromRegisterWithEntreeAndTUser($this->entreeData->user,$attr);
        } else {
            $this->validate($this->rulesStep2());

            $this->registration->save();
            try {
                $this->newRegistration = $this->registration->addUserToRegistration($this->password, $this->registration->invitee, $this->ref);
                $this->step = 3;
            } catch (\Throwable $e) {
                $this->step = 'error';
                Bugsnag::notifyException($e);
            }
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
            if (!$this->btnDisabled) {
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
        $subjects = BaseSubject::where('show_in_onboarding',true)->get()->pluck('name')->toArray();
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
}
