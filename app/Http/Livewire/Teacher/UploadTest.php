<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\FileManagement;
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
        return $this->plannedAt->format('j F Y');
    }

    public function getPlannedAtProperty(): Carbon
    {
        return Carbon::parse($this->testInfo['planned_at'] . ' 00:00:00');
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

    public function getCheckWarningTextProperty(): string
    {
        if ($this->checkedCorrectBoxes) {
            return 'check_correct_text';
        }

        if ($this->checkInfo['answer_model']) {
            return 'check_warning_text_question';
        }

        if ($this->checkInfo['question_model']) {
            return 'check_warning_text_answer';
        }

        return 'check_warning_text';
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

        $this->checkInfo = array_merge($this->checkInfo, [
            'question_model'          => true,
            'answer_model'            => true,
            'attachments'             => true,
            'elaboration_attachments' => true,
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

    public function finishProcess()
    {
        $typedetails = $this->getTypeDetailsForFileManagementModel();

        $parentFM = $this->createParentFileManagementModel($typedetails);

        collect($this->uploads)->each(function ($upload) use ($parentFM, $typedetails) {
            $childId = Uuid::uuid4();
            $uploadFileName = $this->getFileNameForUpload($upload, $childId);

            $childFM = $this->createChildFileManagementModels($upload, $parentFM, $uploadFileName, $childId);

            $upload->storeAs(
                Auth::user()->school_location_id,
                $uploadFileName,
                'test_uploads'
            );
        });
    }

    private function getTypeDetailsForFileManagementModel(): Collection
    {
        return collect($this->testInfo)
            ->reject(function ($item, $key) {
                return collect(['planned_at', 'subject_uuid', 'education_level_uuid', 'test_kind_uuid'])->contains($key);
            })
            ->merge(collect([
                'multiple'           => 0,
                'form_id'            => $this->formUuid,
                'correctiemodel'     => $this->checkInfo['answer_model'] ? 1 : 0,
                'subject_id'         => Subject::whereUuid($this->testInfo['subject_uuid'])->value('id'),
                'education_level_id' => EducationLevel::whereUuid($this->testInfo['education_level_uuid'])->value('id'),
                'test_kind_id'       => TestKind::whereUuid($this->testInfo['test_kind_uuid'])->value('id'),
            ]));
    }

    /**
     * @param Collection $typedetails
     * @return void
     */
    private function createParentFileManagementModel(Collection $typedetails): FileManagement
    {
        return FileManagement::create([
            'id'                        => $this->formUuid,
            'uuid'                      => Uuid::uuid4(),
            'school_location_id'        => Auth::user()->school_location_id,
            'user_id'                   => Auth::id(),
            'origname'                  => $this->testInfo['name'],
            'name'                      => $this->testInfo['name'],
            'test_name'                 => $this->testInfo['name'],
            'education_level_year'      => $this->testInfo['education_level_year'],
            'type'                      => FileManagement::TYPE_TEST_UPLOAD,
            'typedetails'               => $typedetails,
            'file_management_status_id' => 1,
            'planned_at'                => $this->plannedAt,
            'subject_id'                => $typedetails['subject_id'],
            'education_level_id'        => $typedetails['education_level_id'],
            'test_kind_id'              => $typedetails['test_kind_id'],
            'form_id'                   => $this->formUuid,
        ]);
    }

    private function createChildFileManagementModels(TemporaryUploadedFile $upload, ?FileManagement $parentFM, string $storageFileName, string $childId): FileManagement
    {
        return FileManagement::create(
            collect($parentFM->toArray())->merge([
                'id'         => $childId,
                'uuid'       => Uuid::uuid4(),
                'origname'   => $upload->getClientOriginalName(),
                'name'       => $storageFileName,
                'parent_id'  => $parentFM->id,
            ])
                ->toArray()
        );
    }

    private function getFileNameForUpload($upload, $uuid): string
    {
        return sprintf('%s-%s-%s.%s',
            $uuid,
            Str::random(5),
            Str::slug($this->testInfo['name']),
            pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION)
        );
    }

}