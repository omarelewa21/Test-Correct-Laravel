<?php

namespace tcCore\Http\Livewire\Account;

use Illuminate\Support\Collection;
use Livewire\Component;
use tcCore\Http\Enums\GradingStandard;
use tcCore\Http\Enums\SystemLanguage;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Enums\WscLanguage;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Http\Traits\WithReturnHandling;
use tcCore\SchoolLocationUser;
use tcCore\User;
use tcCore\UserFeatureSetting;

class Teacher extends Component
{
    use WithReturnHandling;

    public UserData $userData;
    private User $user;
    public Collection $featureSettings;
    public bool $canEditProfile = false;
    public ?string $editRestriction;

    public array $subjects;
    public array $locations;
    public array $classes;
    public string $locationName;
    public Collection $systemLanguages;
    public Collection $wscLanguages;
    public Collection $gradingStandards;

    protected function getRules(): array
    {
        return UserData::$rules;
    }

    public function mount(User $user): void
    {
        $this->setUserData($user);
        $this->setCanEditProfile();
        $this->setProfileSchoolLocationData();
        $this->setFeatureSettings();
        $this->setSelectOptions();
    }

    public function hydrate(): void
    {
        $this->user = User::whereUuid($this->userData->uuid)->first();
    }

    public function dehydrate(): void
    {
        if ($this->user->isDirty() && $this->canEditProfile) {
            $this->user->save();
        }
    }

    public function updatedFeatureSettings(mixed $value, string $name): void
    {
        if ($enum = UserFeatureSettingEnum::tryFrom($name)) {
            $this->writeFeatureSetting($enum, $value, $name);
        }
    }

    public function updatedFeatureSettingsSystemLanguage(): void
    {
        UserHelper::setSystemLanguage($this->user);
        $this->setSelectOptions();
    }

    public function updatedUserData(mixed $value, string $name): void
    {
        $this->validate();
        if ($this->user->hasAttribute($name)) {
            $this->user->$name = $value;
        };
    }

    public function updatedFeatureSettingsGradeDefaultStandard(string $value): void
    {
        if ($enum = GradingStandard::tryFrom($value)) {
            $this->handleUpdatedGradingStandard($enum);
        }
    }

    public function redirectBack()
    {
        return $this->redirectUsingReferrer();
    }

    private function setCanEditProfile(): void
    {
        $schoolLocation = $this->user->schoolLocation;
        $this->canEditProfile = !$schoolLocation->lvs_active && !$schoolLocation->hasClientLicense();
        if (!$this->canEditProfile) {
            $this->editRestriction = 'lvs';//$schoolLocation->lvs_active ? 'lvs' : 'license';
        }
    }

    private function setUserData(User $user): void
    {
        $this->user = $user;
        $this->userData = $user->getUserDataObject();
    }

    private function setProfileSchoolLocationData(): void
    {
        $this->user->load('schoolLocation:id,name');
        $this->locationName = $this->user->schoolLocation->name;
        collect([
            'subjects'  => $this->user->subjects()->pluck('name'),
            'locations' => SchoolLocationUser::where('school_location_user.user_id', $this->user->getKey())
                ->join('school_locations', 'school_locations.id', '=', 'school_location_user.school_location_id')
                ->get(['school_locations.name', 'school_locations.id'])
                ->sortByDesc(fn($location) => $location->id === $this->user->school_location_id)
                ->map(function ($location) {
                    return sprintf(
                        '<span %s>%s</span>',
                        $location->id === $this->user->school_location_id ? 'class="text-sysbase"' : '',
                        $location->name
                    );
                }),
            'classes'   => $this->user->teacherSchoolClasses()->pluck('name'),
        ])->each(function (Collection $data, string $property) {
            $this->{$property}['count'] = $data->count();
            $this->{$property}['string'] = $data->join(', ');
        });
    }

    private function setFeatureSettings(): void
    {
        $externalDefaults = $this->getExternalDefaultSettingValues();
        $this->featureSettings = UserFeatureSettingEnum::initialValues()
            ->merge($externalDefaults)
            ->merge(UserFeatureSetting::getAll($this->user));

        $this->handleUpdatedGradingStandard(
            GradingStandard::tryFrom($this->featureSettings['grade_default_standard'])
        );
    }

    /**
     * @param UserFeatureSettingEnum $enum
     * @param mixed $value
     * @param string $name
     * @return void
     */
    private function writeFeatureSetting(UserFeatureSettingEnum $enum, mixed $value, string $name): void
    {
        try {
            $enum->validateValue($value);
            UserFeatureSetting::setSetting($this->user, $enum->value, $value);
        } catch (\Throwable $exception) {
            $this->featureSettings[$name] = UserFeatureSettingEnum::getInitialValue($enum);
            $this->addError($name, $exception->getMessage());
        }
    }

    private function getExternalDefaultSettingValues(): array
    {
        return [
            'system_language'      => $this->user->schoolLocation->school_language,
            'wsc_default_language' => $this->user->schoolLocation->wsc_language
        ];
    }

    private function setSelectOptions(): void
    {
        $this->setLanguages();

        $this->gradingStandards = GradingStandard::casesWithDescription();
    }

    private function setLanguages(): void
    {
        $this->systemLanguages = SystemLanguage::casesWithDescription();

        $this->wscLanguages = WscLanguage::casesWithDescription();
    }

    /**
     * @param GradingStandard $enum
     * @return void
     */
    private function handleUpdatedGradingStandard(GradingStandard $enum): void
    {
        if ($enum === GradingStandard::CESUUR) {
            $this->featureSettings['grade_cesuur_percentage'] = UserFeatureSetting::getSetting(
                user   : $this->user,
                title  : UserFeatureSettingEnum::GRADE_CESUUR_PERCENTAGE,
                default: GradingStandard::getInitialValue($enum)
            );
        } else {
            $this->featureSettings['grade_cesuur_percentage'] = null;
        }
    }
}
