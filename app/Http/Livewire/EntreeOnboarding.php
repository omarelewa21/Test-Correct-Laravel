<?php

namespace tcCore\Http\Livewire;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use tcCore\BaseSubject;
use tcCore\DemoTeacherRegistration;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Jobs\SendOnboardingWelcomeMail;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Mail\TeacherRegisteredEntree;
use tcCore\SamlMessage;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationEducationLevel;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\User;

class EntreeOnboarding extends Onboarding
{
    public $saml_id;
    public $registration;
    public $step = 1;

    public $password;
    public $password_confirmation;

    public $btnDisabled = true;
    public $resendVerificationMail = false;
    public $userUuid = false;

    public $warningStepOne = false;
    public $warningStepTwo = false;

    public $warningStepOneConfirmed = false;
    public $warningStepTwoConfirmed = false;
    public $selectedSubjects = [];
    public Collection $subjects;

    public $showSubjects = true;
    public $hasValidTUser = false;
    public $hasFixedLocation = false;
    public $hasFixedEmail = true;
    public $selectedLocationsString = null;
    public $schoolLocation;
    public $schoolLocations = [];
    public $school;
    public $samlId;

    public $needsPassword = true;
    protected $preventFieldTransformation = ['password', 'password_confirmation'];

    protected $queryString = ['step', 'samlId'];

    protected function messages(): array
    {
        return [
            'registration.name_first.required' => __('registration.name_first_required'),
            'registration.name.required'       => __('registration.name_last_required'),
            'registration.gender.required'     => __('registration.gender_required'),
            'selectedLocationsString.required' => __('registration.school_location_required'),
            'registration.username.required'   => __('registration.username_required'),
            'registration.username.email'      => __('registration.username_email'),
            'registration.username.unique'     => __('registration.username_unique'),
            'password.required'                => __('registration.password_required'),
            'password.min'                     => __('registration.password_min'),
            'password.same'                    => __('registration.password_same'),
        ];
    }

    public function rules(): array
    {
        $default = [
            'registration.username'                     => 'required|email:rfc,dns|unique:users,username',
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
            'password'                                  => 'sometimes',
        ];

        if ($this->step === 1) {
            $rules = array_merge($default, [
                'registration.gender'           => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first'       => 'required|string',
                'registration.name'             => 'required|string',
                'registration.name_suffix'      => 'sometimes',
            ]);

            if ($this->needsPassword) {
                $rules = array_merge($rules, [
                    'password' => 'required|same:password_confirmation|' . User::getPasswordLengthRule(),
                ]);
            }

            return $rules;
        }

        return $default;
    }

    public function rulesStep2(): array
    {
        return !$this->hasValidTUser ? ['selectedLocationsString' => 'required',] : [];
    }

    public function mount(): void
    {
        $this->registration = new DemoTeacherRegistration();

        if (!$this->setEntreeDataFromRequestIfAvailable()) {
            return;
        }

        $this->saml_id = $this->entreeData->uuid;

        $this->registration->username = $this->entreeData->data->emailAddress;

        if (!$this->hasValidTUser) {
            $this->registration->name = $this->entreeData->data->lastName;
            $this->registration->name_suffix = $this->entreeData->data->nameSuffix;
            $this->registration->name_first = $this->entreeData->data->firstName;
        } else {
            $this->needsPassword = false;
        }

        if (!$this->step != 1 || $this->step >= '4') {
            $this->step = 1;
        }

        $this->registration->level = "VO";


        $this->registration->registration_email_confirmed = $this->hasValidTUser;
        if (!$this->hasValidTUser) {
            $this->setSubjectOptions();
        }
    }

    public function getEntreeDataProperty(): ?SamlMessage
    {
        return SamlMessage::find($this->saml_id);
    }

    protected function setEntreeDataFromRequestIfAvailable(): bool
    {
        $message = SamlMessage::getSamlMessageIfValid($this->samlId);
        if (!$message) {
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
        }
        if ($this->entreeData->data->schoolId) {
            $this->school = School::find($this->entreeData->data->schoolId);
        }

        $user = null;
        if (property_exists($this->entreeData->data, 'userId')) {

            $user = User::find($this->entreeData->data->userId);
            if ($user && $user->hasImportMailAddress()) {
                $this->hasFixedLocation = true;
                collect(['name_first', 'name_suffix', 'name', 'gender'])->each(function ($key) use ($user) {
                    $this->registration->$key = $user->$key;
                });
                if ($this->school) {
                    $this->schoolLocations = $user->allowedSchoolLocations()->pluck('name')->toArray();
                }
                $this->hasValidTUser = true;
                $this->showSubjects = false;
                $this->btnStepOneDisabledCheck();
            }
        }
        if (null === $user) {
            $this->hasFixedEmail = (bool)$this->entreeData->data->emailAddress;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.entree-onboarding')->layout('layouts.onboarding');
    }

    public function step1(): void
    {
        $this->validate();
        if (!$this->checkInputForLength() && !$this->warningStepOneConfirmed) {
            $this->warningStepOneConfirmed = true;
            return;
        }
        $this->step = 2;
        $this->warningStepOneConfirmed = false;
    }

    public function finish(): void
    {
        $this->step = 4;
    }

    public function step2(): void
    {
        $this->validate();
        if ($this->hasValidTUser) {
            // we need to merge the data with the t user account
            $attr = [
                'mail'  => [$this->registration->username],
                'eckId' => [Crypt::decryptString($this->entreeData->data->encryptedEckId)]
            ];
            EntreeHelper::initAndHandleFromRegisterWithEntreeAndTUser(
                User::find($this->entreeData->data->userId),
                $attr
            );
            return;
        }

        $this->validate($this->rulesStep2());
        $schoolLocationsUuids = $this->getSelectedSchoolLocationCollection();
        if ($schoolLocationsUuids->count() < 1) {
            $url = BaseHelper::getLoginUrlWithOptionalMessage(
                __(
                    'onboarding-welcome.De gekozen school locatie kon niet gevonden worden. Neem contact op met support.'
                ),
                true
            );
            $this->redirect($url);
            return;
        }

        $schoolLocations = SchoolLocation::whereUuid($schoolLocationsUuids->toArray())->get();
        if ($schoolLocations->count() < 1) {
            $url = BaseHelper::getLoginUrlWithOptionalMessage(
                __(
                    'onboarding-welcome.De gekozen school locatie kon niet gevonden worden. Neem contact op met support.'
                ),
                true
            );
            $this->redirect($url);
            return;
        }

        DB::beginTransaction();
        try {
            $actingAsUser = $schoolLocations->first()->users()->first();
            ActingAsHelper::getInstance()->setUser($actingAsUser);

            $user = (new UserHelper())->createUserFromData([
                    'school_id'          => null,
                    'school_location_id' => $schoolLocations->first()->getKey(),
                    'username'           => $this->registration->username,
                    'password'           => $this->password,
                    'gender'             => $this->registration->gender,
                    'name_first'         => $this->registration->name_first,
                    'name_suffix'        => $this->registration->name_suffix,
                    'name'               => $this->registration->name,
                    'send_welcome_email' => false,
                    'user_roles'         => [1],
                ]
            );
            $this->userUuid = $user->uuid;
            $user->eckid = Crypt::decryptString($this->entreeData->data->encryptedEckId);
            if ($this->hasFixedEmail) {
                $user->account_verified = Carbon::now();
            }
            $user->save();
            $user->generalTermsLog()->create(['accepted_at' => Carbon::now()]);

            $locationsAdded = collect([$user->school_location_id]);

            $schoolLocations->each(function (SchoolLocation $schoolLocation) use ($user, $locationsAdded) {
                // do not add first school location as it is set at registration
                if (!$locationsAdded->contains($schoolLocation->getKey())) {
                    $user->school_location_id = $schoolLocation->getKey();
                    $user->save();
                    $user->addSchoolLocationAndCreateDemoEnvironment($schoolLocation);
                    $user->refresh();
                    $locationsAdded->push($schoolLocation->getKey());
                }
                ActingAsHelper::getInstance()->setUser($user);
                DemoTeacherRegistration::registerIfApplicable($user);

                $currentSchoolYearId = SchoolYearRepository::getCurrentSchoolYear()->getKey();
                $schoolLocation->schoolLocationEducationLevels->each(
                    function (SchoolLocationEducationLevel $slEl) use (
                        $schoolLocation,
                        $user,
                        $currentSchoolYearId
                    ) {
                        $class = new SchoolClass();
                        $class->fill([
                            'visible'                         => false,
                            'school_location_id'              => $schoolLocation->getKey(),
                            'education_level_id'              => $slEl->education_level_id,
                            'school_year_id'                  => $currentSchoolYearId,
                            'name'                            => sprintf(
                                'entree_registration_class_locationid_%s_userid_%s_elid_%s',
                                $schoolLocation->getKey(),
                                $user->getKey(),
                                $slEl->education_level_id
                            ),
                            'education_level_year'            => 1,
                            'is_main_school_class'            => 0,
                            'do_not_overwrite_from_interface' => 0,
                            'demo'                            => 0,
                        ]);
                        $class->save();

                        $this->getSubjectIdsForSchoolLocationAsCollection($schoolLocation)->each(
                            function ($subjectId) use ($user, $class) {
                                Teacher::create([
                                    'subject_id' => $subjectId,
                                    'user_id'    => $user->getKey(),
                                    'class_id'   => $class->getKey(),
                                ]);
                            }
                        );
                    }
                );
            });

            try {
                Mail::to($this->registration->username)->queue(
                    new SendOnboardingWelcomeMail($user, '', $this->hasFixedEmail)
                );
                Mail::to(config('mail.from.address'))->queue(new TeacherRegisteredEntree($user->getKey()));
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
            }

            $this->step = 3;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->step = 'error';
            Bugsnag::notifyException($e);
        }
        DB::commit();
    }

    protected function getSubjectIdsForSchoolLocationAsCollection(SchoolLocation $schoolLocation)
    {
        $baseSubjectIds = $this->getSelectedBaseSubjectIds();
        $sectionsBuilder = $schoolLocation->schoolLocationSections()->select('section_id');
        return Subject::whereIn('section_id', $sectionsBuilder)->whereIn('base_subject_id', $baseSubjectIds->toArray())->pluck('id');
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


    public function fillSchoolData(SchoolLocation $schoolInfo): void
    {
        $this->registration->school_location = $schoolInfo->name;
        $this->registration->address = $schoolInfo->visit_address;
        $this->registration->postcode = $schoolInfo->visit_postal;
        $this->registration->house_number = filter_var($schoolInfo->visit_address, FILTER_SANITIZE_NUMBER_INT);
        $this->registration->city = $schoolInfo->visit_city;
    }

    public function updated($propertyName): void
    {
        $this->btnDisabled = false;

        if ($this->registration->gender != 'different') {
            $this->registration->gender_different = '';
        }
    }

    protected function getSelectedSchoolLocationCollection()
    {
        if (strlen($this->selectedLocationsString) > 0) {
            return collect(json_decode($this->selectedLocationsString));
        }
        return collect([]);
    }

    public function toggleSchoolLocation($uuid, $add = true): void
    {
        $coll = $this->getSelectedSchoolLocationCollection();
        if ($add) {
            $newColl = $coll->push($uuid)->unique();
            $this->saveSelectedSchoolLocationsToString($newColl->all());
            return;
        }
        $this->deleteSchoolLocation($uuid);
    }

    protected function saveSelectedSchoolLocationsToString($coll = null)
    {
        if (null === $coll || count($coll) < 1) {
            $this->selectedLocationsString = null;
            return;
        }

        $this->selectedLocationsString = json_encode(array_values($coll), JSON_HEX_APOS);
    }

    public function isSelectedSchoolLocation($uuid)
    {
        $coll = $this->getSelectedSchoolLocationCollection();
        return $coll->contains($uuid);
    }

    public function deleteSchoolLocation($uuid)
    {
        $newColl = $this->getSelectedSchoolLocationCollection()->filter(function ($val, $key) use ($uuid) {
            return $val !== $uuid;
        });

        $this->saveSelectedSchoolLocationsToString($newColl->isEmpty() ? null : $newColl->all());
    }

    protected function getSelectedBaseSubjectIds(): Collection
    {
        $names = $this->subjects->whereIn('value', $this->selectedSubjects)->pluck('label');
        return BaseSubject::whereIn('name', $names)->where('show_in_onboarding', 1)->pluck('id');
    }

    public function selectedSchoolLocationList()
    {
        return $this->school->schoolLocations->filter(function ($location) {
            return $this->isSelectedSchoolLocation($location->uuid);
        });
    }

}
