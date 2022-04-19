<?php

namespace tcCore\Http\Livewire;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\DemoTeacherRegistration;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Requests\Request;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Factory;
use tcCore\SamlMessage;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Shortcode;
use tcCore\ShortcodeClick;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\TemporaryLogin;
use tcCore\User;

class EntreeOnboarding extends Component
{
    public $saml_id;
    public $registration;
    public $step = 1;

    public $btnDisabled = true;
    public $resendVerificationMail = false;
    public $userUuid = false;

    public $warningStepOne = false;
    public $warningStepTwo = false;

    public $warningStepOneConfirmed = false;
    public $warningStepTwoConfirmed = false;
    public $subjectOptions = '';
    public $selectedSubjects = [];
    public $selectedSubjectsString = '';

    public $showSubjects = true;
    public $hasValidTUser = false;
    public $hasFixedLocation = false;
    public $selectedLocationsString = null;
    public $schoolLocation;
    public $school;
    public $samlId;

    protected $queryString = ['step','samlId'];

    protected function messages()
    {
        return [
            'registration.name_first.required' => __('registration.name_first_required'),
            'registration.name.required' => __('registration.name_last_required'),
            'registration.gender.required' => __('registration.gender_required'),
            'selectedLocationsString.required' => __('registration.school_location_required'),
            'registration.username.required' => __('registration.username_required'),
            'registration.username.email' => __('registration.username_email'),
        ];
    }

    public function rules()
    {
        $default = [
            'registration.username' => 'required|email:rfc,dns',
            'registration.registration_email_confirmed' => 'sometimes',
            'registration.school_location'              => 'sometimes',
            'registration.website_url'                  => 'sometimes',
            'registration.address'                      => 'sometimes',
            'registration.house_number'                 => 'sometimes',
            'registration.postcode'                     => 'sometimes',
            'registration.city'                         => 'sometimes',
            'registration.gender'                       => 'sometimes',
            'registration.gender_different'             => 'sometimes',
            'registration.name_first'                   => 'sometimes',
            'registration.name'                         => 'sometimes',
            'registration.name_suffix'                  => 'sometimes',
            'registration.subjects'                     => 'sometimes',
            ];

        if ($this->step === 1) {
            $rules = array_merge($default, [
                'registration.gender' => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first' => 'required|string',
                'registration.name' => 'required|string',
                'registration.name_suffix' => 'sometimes',
            ]);
            return $rules;
        }

        return $default;
    }

    public function rulesStep2()
    {
        if (!$this->hasValidTUser) {
            return [
                'selectedLocationsString' => 'required',
            ];
        } else {
            return [];
        }
    }

    public function mount()
    {
        $this->registration = new DemoTeacherRegistration;

        if (!$this->setEntreeDataFromRequestIfAvailable()) {
            return true;
        }

        $this->saml_id = $this->entreeData->uuid;

        $this->registration->username = $this->entreeData->data->emailAddress;

        if(!$this->hasValidTUser) {
            $this->registration->name = $this->entreeData->data->lastName;
            $this->registration->name_suffix = $this->entreeData->data->nameSuffix;
            $this->registration->name_first = $this->entreeData->data->firstName;
        }

        if (!$this->step != 1 || $this->step >= '4') {
            $this->step = 1;
        }


        $this->registration->registration_email_confirmed = $this->hasValidTUser;
        if (!$this->hasValidTUser) {
            $this->setSubjectOptions();
        }
    }

    public function getEntreeDataProperty()
    {
        return SamlMessage::find($this->saml_id);
    }

    protected function setEntreeDataFromRequestIfAvailable()
    {
        $message = SamlMessage::getSamlMessageIfValid($this->samlId);
        if(!$message) {
            redirect::to(route('onboarding.welcome'));
            return false;
        }
        $this->entreeData = $message;
        if (!$this->entreeData->data) {
            Redirect::to(route('onboarding.welcome'));
            return false;
        }

        if ($this->entreeData->data->locationId) {
            $this->schoolLocation = SchoolLocation::findOrFail($this->entreeData->data->locationId);
            $this->hasFixedLocation = true;
            $this->saveSelectedSchoolLocationsToString([$this->schoolLocation->uuid]);
        } else if ($this->entreeData->data->schoolId) {
            $this->school = School::find($this->entreeData->data->schoolId);
        }

        if (property_exists($this->entreeData->data, 'userId')) {

            $user = User::find($this->entreeData->data->userId);
            if ($user && $user->hasImportMailAddress()) {
                collect(['name_first', 'name_suffix', 'name', 'gender'])->each(function ($key) use ($user){
                    $this->registration->$key = $user->$key;
                });

                $this->hasValidTUser = true;
                $this->showSubjects = false;
                $this->btnStepOneDisabledCheck();
            }
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
        switch ($this->step) {
            case 1:
                $this->setSelectedSubjectsString();
                $this->setSubjectOptions();
                break;
            case 2:


                break;
        }

        return view('livewire.entree-onboarding')->layout('layouts.onboarding');
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

    public function finish()
    {
        $this->step = 4;
    }

    public function step2()
    {
        $this->validate();

        if (!$this->checkInputForLength() && !$this->warningStepTwoConfirmed) {
            $this->warningStepTwoConfirmed = true;
            return;
        }
        if ($this->hasValidTUser) {
            // we need to merge the data with the t user account
            $attr = [
                'mail' => [$this->registration->username],
                'eckId' => [Crypt::decryptString($this->entreeData->data->encryptedEckId)]
            ];
            return EntreeHelper::initAndHandleFromRegisterWithEntreeAndTUser(User::find($this->entreeData->data->userId), $attr);
        } else {
            $this->validate($this->rulesStep2());
            $schoolLocationsUuids = $this->getSelectedSchoolLocationCollection();
            if($schoolLocationsUuids->count() < 1){
                $url = BaseHelper::getLoginUrlWithOptionalMessage(__('onboarding-welcome.De gekozen school locatie kon niet gevonden worden. Neem contact op met support.'), true);
                return $this->redirectToUrlAndExit($url);
            }
            $schoolLocations = SchoolLocation::whereUuid($schoolLocationsUuids->toArray())->get();

            if($schoolLocations->count() < 1){
                $url = BaseHelper::getLoginUrlWithOptionalMessage(__('onboarding-welcome.De gekozen school locatie kon niet gevonden worden. Neem contact op met support.'), true);
                return $this->redirectToUrlAndExit($url);
            }

            DB::beginTransaction();
            try {
                $userFactory = new Factory(new User());
                $user = $userFactory->generate([
                        'school_id' => null,
                        'school_location_id' => $schoolLocations->first()->getKey(),
                        'username' => $this->registration->username,
                        'password' => '',
                        'gender' => $this->registration->gender,
                        'name_first' => $this->registration->name_first,
                        'name_suffix' => $this->registration->name_suffix,
                        'name' => $this->registration->name,
                        'send_welcome_email' => false,
                        'user_roles' => [1],
                    ],
                    true
                );
                $this->userUuid = $user->uuid;
                $user->eckid = Crypt::decryptString($this->entreeData->data->encryptedEckId);
                $user->account_verified = Carbon::now();
                $user->save();

                if ($schoolLocations->count() > 0) {
                    $schoolLocations->each(function (SchoolLocation $schoolLocation) use ($user) {
                        $user->addSchoolLocation($schoolLocation);
                        $user->school_location_id = $schoolLocation->getKey();
                        $user->save();
                        $user->refresh();
                        ActingAsHelper::getInstance()->setUser($user);
                        $class = new SchoolClass();
                        $class->fill([
                            'visible' => false,
                            'school_location_id' => $schoolLocation->getKey(),
                            'education_level_id' => $schoolLocation->schoolLocationEducationLevels->first()->value('education_level_id'),
                            'school_year_id' => SchoolYearRepository::getCurrentOrPreviousSchoolYear()->getKey(),
                            'name' => sprintf('entree_registration_class_%s', $user->getKey()),
                            'education_level_year' => 1,
                            'is_main_school_class' => 0,
                            'do_not_overwrite_from_interface' => 0,
                            'demo' => 0,
                        ]);
                        $class->save();

                        $this->getSubjectIdsForSchoolLocationAsCollection($schoolLocation)->each(function ($subjectId) use ($user, $class) {
                            Teacher::create([
                                'subject_id' => $subjectId,
                                'user_id' => $user->getKey(),
                                'class_id' => $class->getKey(),
                            ]);
                        });
                    });
                }
                $this->step = 3;
            } catch (\Throwable $e){
                DB::rollBack();
                dd($e);
                $this->step = 'error';
                Bugsnag::notifyException($e);
            }
            DB::commit();;
        }
    }

    protected function getSubjectIdsForSchoolLocationAsCollection(SchoolLocation $schoolLocation)
    {
        $baseSubjectIds = $this->getSelectedBaseSubjectIds();
        $sections = $schoolLocation->schoolLocationSections()->pluck('section_id');
        return Subject::whereIn('section_id',$sections->toArray())->whereIn('base_subject_id',$baseSubjectIds->toArray())->pluck('id');
    }

    public function loginUser()
    {
        $redirectUrl = config('app.url_login');
        if ($this->userUuid) {
            $user = User::whereUuid($this->userUuid)->first();
            if ($user) {
                $temporaryLogin = TemporaryLogin::create(
                    ['user_id' => $user->getKey()]
                );
                $redirectUrl = $temporaryLogin->createCakeUrl();
            }
        }
        Redirect::to($redirectUrl);
    }

    private function btnStepOneDisabledCheck()
    {
        if ($this->step == 1) {
            $this->btnDisabled = (
                empty($this->registration->name_first)
                || empty($this->registration->gender)
                || empty($this->registration->name)
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

        if ($this->registration->gender != 'different') {
            $this->registration->gender_different = '';
        }

        $this->validateOnly($propertyName);
    }

    protected function getSelectedSchoolLocationCollection()
    {
        if (strlen($this->selectedLocationsString) > 0) {
            return collect(json_decode($this->selectedLocationsString));;
        }
        return collect([]);
    }

    public function toggleSchoolLocation($uuid, $add = true)
    {
        $coll = $this->getSelectedSchoolLocationCollection();
        if ($add) {
            $newColl = $coll->push($uuid)->unique();
            return $this->saveSelectedSchoolLocationsToString($newColl->all());
        }
        return $this->deleteSchoolLocation($uuid);
    }

    protected function saveSelectedSchoolLocationsToString($coll = null)
    {
        if(null === $coll || count($coll) < 1){
            $this->selectedLocationsString = null;
        }
        $this->selectedLocationsString = json_encode($coll,JSON_HEX_APOS);
    }

    public function isSelectedSchoolLocation($uuid)
    {
        $coll = $this->getSelectedSchoolLocationCollection();
        return $coll->contains($uuid);
    }

    public function deleteSchoolLocation($uuid)
    {
        $coll = $this->getSelectedSchoolLocationCollection();
        $newColl = $coll->filter(function ($val, $key) use ($uuid) {
            return $val !== $uuid;
        });
        $this->saveSelectedSchoolLocationsToString($newColl->all());
    }

    protected function getSelectedBaseSubjectIds()
    {
        $names = json_decode($this->selectedSubjectsString);
        return BaseSubject::whereIn('name',$names)->where('show_in_onboarding',1)->pluck('id');
    }

    public function syncSelectedSubjects($subjects)
    {
        $this->registration->subjects = implode(';', $subjects);
        $this->selectedSubjects = $subjects;
    }

    protected function setSubjectOptions()
    {
        $subjects = BaseSubject::where('show_in_onboarding', true)->get()->pluck('name')->toArray();
        $subjects = array_unique($subjects);
        sort($subjects);
//        $subjects = $this->translateSubjects($subjects);
        $subjects = array_diff($subjects, $this->selectedSubjects);
        $this->subjectOptions = json_encode($subjects, JSON_HEX_APOS);
    }

    protected function setSelectedSubjectsString()
    {
        $this->selectedSubjectsString = json_encode($this->selectedSubjects, JSON_HEX_APOS);
    }

    private function translateSubjects($subjects)
    {
        return collect($subjects)->map(function ($subject) {
            return __('subject.' . $subject);
        })->toArray();
    }
}
