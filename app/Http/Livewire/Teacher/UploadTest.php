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

    public array $checkInfo = [
        'question_model'          => false,
        'answer_model'            => false,
        'attachments'             => false,
        'elaboration_attachments' => false,
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

    public function getSubjectsProperty(): \Countable
    {
        return Subject::filtered(
            ['user_current' => auth()->id()],
            ['name' => 'asc']
        )
            ->uuidOptionList();
    }

    public function getEducationLevelsProperty(): \Countable
    {
        return EducationLevel::filtered(['user_id' => auth()->id()], [])
            ->uuidOptionList();
    }

    public function getTestKindsProperty(): \Countable
    {
        return TestKind::orderBy('name')
            ->uuidOptionList();
    }

    public function getTakeDateToDisplayProperty(): string
    {
        Carbon::setlocale(config('app.locale'));
        return Carbon::parse($this->testInfo['planned_at'] . ' 00:00:00')->format('j F Y');
    }

    public function getSelectedSubjectProperty(): string
    {
        return $this->getSelectedItem('subjects');
    }

    public function getSelectedLevelProperty(): string
    {
        return $this->getSelectedItem('educationLevels');
    }

    public function getSelectedTestKindProperty(): string
    {
        return $this->getSelectedItem('testKinds');
    }

    public function getCheckedCorrectBoxesProperty(): bool
    {
        return $this->checkInfo['answer_model'] && $this->checkInfo['question_model'];
    }

    public function accordionUpdate($value): void {}

    private function setDummyData()
    {
        $this->testInfo = array_merge($this->testInfo, [
            'name'                       => 'kaas',
            'subject_uuid'               => $this->subjects->first()?->value,
            'education_level_uuid'       => $this->educationLevels->first()?->value,
            'education_level_year'       => 3,
            'test_kind_uuid'             => $this->testKinds->first()?->value,
            'contains_publisher_content' => true,
        ]);
    }

    /**
     * @param $property
     * @return string
     */
    private function getSelectedItem($property): string
    {
        $identifier = str($property)->snake()->replaceLast('s', '_uuid')->value();

        if (blank($this->testInfo[$identifier])) {
            return '';
        }
        $selectedItem = collect($this->$property)->where('value', $this->testInfo[$identifier])->first();
        if (!$selectedItem) {
            return '';
        }
        return $selectedItem->label ?? '';
    }
}