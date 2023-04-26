<?php

namespace tcCore\Http\Livewire\Account;

use Illuminate\Support\Collection;
use Livewire\Component;
use tcCore\Http\Traits\WithReturnHandling;
use tcCore\SchoolLocationUser;
use tcCore\User;
use tcCore\UserFeatureSetting;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;

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
    }

    public function hydrate(): void
    {
        $this->user = User::whereUuid($this->userData->uuid)->first();
    }

    public function dehydrate(): void
    {
        if ($this->user->isDirty()) {
            $this->user->save();
        }
    }

    public function updatedFeatureSettings(mixed $value, string $name): void
    {
        if ($enum = UserFeatureSettingEnum::tryFrom($name)) {
            $this->writeFeatureSetting($enum, $value, $name);
        }
    }

    public function updatedUserData(mixed $value, string $name): void
    {
        $this->validate();
        if ($this->user->hasAttribute($name)) {
            $this->user->$name = $value;
        };
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
            $this->editRestriction = $schoolLocation->lvs_active ? 'lvs' : 'license';
        }
    }

    private function setUserData(User $user): void
    {
        $this->userData = new UserData([
            'username'    => $user->username,
            'uuid'        => $user->uuid,
            'name_first'  => $user->name_first,
            'name_suffix' => $user->name_suffix,
            'name'        => $user->name,
            'gender'      => $user->gender,
        ]);
        $this->user = $user;
    }

    private function setProfileSchoolLocationData(): void
    {
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
        $this->featureSettings = UserFeatureSettingEnum::initialValues()
            ->merge(UserFeatureSetting::getAll($this->user));
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
}
