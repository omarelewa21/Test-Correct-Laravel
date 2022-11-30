<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Subject;
use tcCore\TemporaryLogin;
use tcCore\TestKind;

class UploadTest extends Component
{
    use WithFileUploads;

    public string $formUuid;
    public array $testInfo = [
        'name'                       => '',
        'planned_at'                 => null,
        'subject_uuid'               => 0,
        'education_level_uuid'       => 0,
        'education_level_year'       => 0,
        'test_kind_uuid'             => 0,
        'contains_publisher_content' => null,
    ];

    public bool $tabOneComplete = false;
    public bool $tabTwoComplete = false;
    public bool $showDateWarning = false;
    public string $minimumTakeDate;

    public $uploads = [];

    public function getListeners()
    {
        return ['accordion-update' => 'accordionUpdate'];
    }

    public function mount()
    {
        $this->formUuid = Uuid::uuid4();
        $this->setDateProperties();

        if (BaseHelper::notProduction()) {
            $this->setDummyData();
        }
    }

    public function updated($name, $value)
    {
        $this->tabOneComplete = collect($this->testInfo)->reject(fn($item) => filled($item))->isEmpty();
        $this->tabTwoComplete = collect($this->uploads)->isNotEmpty();
    }

    public function updatingContainsPublisherContent($value)
    {
        $this->contains_publisher_content = $value === 'yes';
    }

    public function updatedTestInfoPlannedAt($value)
    {
        $this->testInfo['planned_at'] = $this->testInfo['planned_at'] . ' 00:00:00';
        $this->showDateWarning = Carbon::parse($value)->isBefore(Carbon::parse($this->minimumTakeDate)->addDays(7));
    }

    public function render()
    {
        return view('livewire.teacher.upload-test')->layout('layouts.base');
    }

    public function back()
    {
        return redirect(TemporaryLogin::createForUser(Auth::user())->createCakeUrl());
    }

    /**
     * @return void
     */
    private function setDateProperties(): void
    {
        $this->minimumTakeDate = Carbon::now()->addDays(7)->toDateString();
        $this->testInfo['planned_at'] = Carbon::now()->addMonth()->toDateString();
    }

    public function getSubjectsProperty(): array
    {
        return Subject::filtered(
            ['user_current' => auth()->id()],
            ['name' => 'asc']
        )
            ->select('uuid', 'name')
            ->get()
            ->map(fn($subject) => ['value' => $subject->uuid, 'label' => $subject->name])
            ->toArray();
    }

    public function getEducationLevelsProperty(): array
    {
        return EducationLevel::filtered(['user_id' => auth()->id()], [])
            ->select(['uuid', 'name'])
            ->get()
            ->map(fn($subject) => ['value' => $subject->uuid, 'label' => $subject->name])
            ->toArray();
    }

    public function getTestKindsProperty(): array
    {
        return TestKind::orderBy('name')
            ->select(['uuid', 'name'])
            ->get()
            ->map(fn($subject) => ['value' => $subject->uuid, 'label' => $subject->name])
            ->toArray();
    }

    public function accordionUpdate($value): void {}

    private function setDummyData()
    {
        $this->testInfo = array_merge($this->testInfo, [
            'name'                       => 'kaas',
            'subject_uuid'               => $this->subjects[0]['value'],
            'education_level_uuid'       => $this->educationLevels[0]['value'],
            'education_level_year'       => 3,
            'test_kind_uuid'             => $this->testKinds[0]['value'],
            'contains_publisher_content' => true,
        ]);
    }
}