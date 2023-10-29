<?php

namespace tcCore\Http\Livewire\Teacher;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Exceptions\UploadTestException;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Http\Traits\WithReturnHandling;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\Modal\TestActions;
use tcCore\Subject;
use tcCore\TestKind;

class UploadTest extends TCComponent
{
    use WithFileUploads;
    use WithReturnHandling;
    use TestActions;

    const CAKE_RETURN_ROUTE_SESSION_KEY = 'upload_test_cake_return_route';
    const LARAVEL_RETURN_ROUTE_SESSION_KEY = 'upload_test_laravel_return_route';
    const MAX_FILE_SIZE_IN_BYTES = 64000000;

    public array $testInfo = [
        'name'                       => '',
        'planned_at'                 => null,
        'subject_uuid'               => null,
        'education_level_uuid'       => null,
        'education_level_year'       => null,
        'test_kind_uuid'             => null,
        'contains_publisher_content' => null,
    ];

    public array $checkInfo = [
        'question_model'          => false,
        'answer_model'            => false,
        'attachments'             => false,
        'elaboration_attachments' => false,
    ];
    public string $formUuid;
    public bool $tabOneComplete = false;
    public bool $tabTwoComplete = false;
    public bool $showDateWarning = false;
    public string $minimumTakeDate;
    public mixed $uploads = [];
    public array $uploadRules = [];
    public bool $canUseTestUploader = true;
    public array $previousUploadedTestNames = [];
    public int $uploadedTests = 0;
    public Carbon|null $plannedAt = null;

    public function mount()
    {
        $this->canUseTestUploader = !SchoolHelper::isTempTeachersSchoolLocation(Auth::user()->schoolLocation);
        $this->setFormUuid();
        $this->setDateProperties();
        $this->setUploadRules();
    }

    public function updating()
    {
        if (!$this->canUseTestUploader) abort(403);
    }

    public function updated($name, $value)
    {
        $this->tabOneComplete = $this->validatePropertiesToCompleteTabOne();
        $this->tabTwoComplete = collect($this->uploads)->isNotEmpty();
    }

    public function updatedUploads($value)
    {
        $this->validate([
            'uploads.*' => 'max:' . self::MAX_FILE_SIZE_IN_BYTES / 1000, /* Have to convert bytes to KBs */
        ]);
    }

    public function updatingContainsPublisherContent($value)
    {
        $this->contains_publisher_content = $value === 'yes';
    }

    public function updatedTestInfoPlannedAt($value)
    {
        $this->showDateWarning = Carbon::parse($value)->isBefore(Carbon::parse($this->minimumTakeDate)->addDays(7));
        $this->plannedAt =  Carbon::parse($value . ' 00:00:00');
    }

    public function render()
    {
        return view('livewire.teacher.upload-test')->layout('layouts.base');
    }

    public function back()
    {
        return $this->redirectUsingReferrer();
    }

    /**
     * @return void
     */
    private function setDateProperties(): void
    {
        $this->minimumTakeDate = Carbon::now()->addDays(7)->toDateString();
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

    public function getEducationLevelYearsProperty(): array
    {
        if ($this->hasSelectedEducationLevelYear()) {
            return $this->educationLevelYearOptionsForSelectedLevel();
        }

        return [
            ['value' => 1, 'label' => '1'],
            ['value' => 2, 'label' => '2'],
            ['value' => 3, 'label' => '3'],
            ['value' => 4, 'label' => '4'],
            ['value' => 5, 'label' => '5'],
            ['value' => 6, 'label' => '6'],
        ];
    }

    public function updatedTestInfoEducationLevelUuid($value)
    {
        $this->testInfo['education_level_year'] = null;
    }

    public function getTestKindsProperty(): \Countable
    {
        return TestKind::orderBy('name')
            ->uuidOptionList(['uuid', 'name'], fn($label) => __('teacher.test-type-' . $label['name']));
    }

    public function getTakeDateToDisplayProperty(): string|null
    {
        return $this->plannedAt?->format('j F Y');
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
        return html_entity_decode($selectedItem->label) ?? '';
    }

    public function finishProcess(bool $openSuccessModal = true)
    {
        $this->validateTestName()->validate();

        $typedetails = $this->getTypeDetailsForFileManagementModel();

        $parentFileManagement = $this->createParentFileManagementModel($typedetails);

        try {
            collect($this->uploads)->each(function ($upload) use ($parentFileManagement, $typedetails) {
                $childId = Uuid::uuid4();
                $uploadFileName = $this->getFileNameForUpload($upload, $childId);

                $this->createChildFileManagementModels($upload, $parentFileManagement, $uploadFileName, $childId);

                $upload->storeAs(
                    Auth::user()->school_location_id,
                    $uploadFileName,
                    'test_uploads'
                );
            });
        } catch (UploadTestException $exception) {
            \Bugsnag::notifyException($exception);
            $this->dispatchBrowserEvent('notify', ['message' => __('auth.something_went_wrong'), 'error']);
        }

        if ($openSuccessModal) {
            $this->emit('openModal', 'teacher.upload-test-success-modal');
        }

        $this->setFormUuid();
    }

    /**
     * @return Collection
     */
    private function getTypeDetailsForFileManagementModel(): Collection
    {
        return collect($this->testInfo)
            ->reject(function ($item, $key) {
                return collect(['planned_at', 'subject_uuid', 'education_level_uuid', 'test_kind_uuid'])->contains($key);
            })
            ->merge([
                'multiple'           => 0,
                'form_id'            => $this->formUuid,
                'correctiemodel'     => $this->checkInfo['answer_model'] ? 1 : 0,
                'subject_id'         => Subject::whereUuid($this->testInfo['subject_uuid'])->value('id'),
                'education_level_id' => EducationLevel::whereUuid($this->testInfo['education_level_uuid'])->value('id'),
                'test_kind_id'       => TestKind::whereUuid($this->testInfo['test_kind_uuid'])->value('id'),
            ]);
    }

    /**
     * @param Collection $typedetails
     * @return FileManagement
     */
    private function createParentFileManagementModel(Collection $typedetails): FileManagement
    {
        return FileManagement::create([
            'id'                         => $this->formUuid,
            'uuid'                       => Uuid::uuid4(),
            'school_location_id'         => Auth::user()->school_location_id,
            'user_id'                    => Auth::id(),
            'origname'                   => $this->testInfo['name'],
            'name'                       => $this->testInfo['name'],
            'test_name'                  => $this->testInfo['name'],
            'education_level_year'       => $this->testInfo['education_level_year'],
            'type'                       => FileManagement::TYPE_TEST_UPLOAD,
            'typedetails'                => $typedetails,
            'file_management_status_id'  => FileManagementStatus::STATUS_PROVIDED,
            'planned_at'                 => $this->plannedAt,
            'subject_id'                 => $typedetails['subject_id'],
            'education_level_id'         => $typedetails['education_level_id'],
            'test_kind_id'               => $typedetails['test_kind_id'],
            'form_id'                    => $this->formUuid,
            'contains_publisher_content' => $typedetails['contains_publisher_content'],
        ]);
    }

    /**
     * @param TemporaryUploadedFile $upload
     * @param FileManagement|null $parentFileManagement
     * @param string $storageFileName
     * @param string $childId
     * @return FileManagement
     */
    private function createChildFileManagementModels(TemporaryUploadedFile $upload, ?FileManagement $parentFileManagement, string $storageFileName, string $childId): FileManagement
    {
        return FileManagement::create(
            array_merge(
                $parentFileManagement->toArray(),
                [
                    'id'         => $childId,
                    'uuid'       => Uuid::uuid4(),
                    'origname'   => $upload->getClientOriginalName(),
                    'name'       => $storageFileName,
                    'parent_id'  => $parentFileManagement->id,
                    'planned_at' => $parentFileManagement->planned_at,
                ]
            )
        );
    }

    /**
     * @param $upload
     * @param $uuid
     * @return string
     */
    private function getFileNameForUpload($upload, $uuid): string
    {
        return sprintf('%s-%s-%s.%s',
            $uuid,
            Str::random(5),
            Str::slug($this->testInfo['name']),
            pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION)
        );
    }

    public function setUploadRules(): void
    {
        $mimeString = config('livewire.temporary_file_upload.rules')[0];
        $mimeArray = str(str($mimeString)->explode(':')[1])->explode(',')->toArray();

        $this->uploadRules = [
            'extensions' => ['data' => $mimeArray, 'message' => __('upload.upload_rule_extension')],
            'size'       => ['data' => self::MAX_FILE_SIZE_IN_BYTES, 'message' => __('upload.upload_rule_size')]
        ];
    }

    private function setFormUuid()
    {
        $this->formUuid = Uuid::uuid4();
    }

    /**
     * @return bool
     */
    private function hasSelectedEducationLevelYear(): bool
    {
        return is_string($this->testInfo['education_level_uuid']) && Uuid::isValid($this->testInfo['education_level_uuid']);
    }

    private function educationLevelYearOptionsForSelectedLevel(): array
    {
        $maxYears = EducationLevel::whereUuid($this->testInfo['education_level_uuid'])->first()->max_years;

        return collect(range(1, $maxYears))
            ->map(fn($value) => ['value' => (int)$value, 'label' => (string)$value])
            ->toArray();
    }

    public function handleUploadPermissionsForUser()
    {
        if (!$this->canUseTestUploader) {
            $this->emit('openModal', 'teacher.upload-test-not-allowed-modal');
        }
    }

    public function uploadAnotherTest(bool $keepTestInfo): bool
    {
        $this->finishProcess(false);

        $this->previousUploadedTestNames[] = $this->testInfo['name'];
        $this->uploadedTests++;

        $this->resetPropertiesToComponentDefault($keepTestInfo);

        return true;
    }

    private function resetPropertiesToComponentDefault(bool $keepTestInfo): void
    {
        $propertiesToReset = ['checkInfo', 'uploads', 'tabOneComplete', 'tabTwoComplete'];
        if (!$keepTestInfo) {
            $propertiesToReset = [...$propertiesToReset, 'testInfo', 'plannedAt', 'showDateWarning'];
        }
        $this->reset(...$propertiesToReset);

        if (!$keepTestInfo) {
            $this->setDateProperties();
            $this->dispatchBrowserEvent('clear-datepicker');
        }
    }

    private function validateTestName()
    {
        $rules = $this->getNameRulesDependingOnAction();
        if(!auth()->user()->isToetsenbakker()){
            $rules[] = Rule::notIn($this->previousUploadedTestNames);
            $rules[] = Rule::unique('file_managements', 'name')
                ->where(function(Builder $query){
                    $query->where('user_id', auth()->id())
                        ->whereExists(fn(Builder $query) => $query->select(DB::raw(1))
                            ->from('file_management_statuses as fms')
                            ->whereColumn('fms.id', 'file_managements.file_management_status_id')
                            ->where('fms.id', '<>', FileManagementStatus::STATUS_CANCELLED));
                });
        }

        return Validator::make($this->testInfo, [
            'name' => $rules
        ], [
            'name.not_in' => __('upload.validation.name.not_in'),
            'name.unique' => __('upload.validation.name.unique'),
            'name.min:3'  => __('upload.validation.name.min'),
        ]);
    }

    /**
     * @return bool
     */
    private function validatePropertiesToCompleteTabOne(): bool
    {
        return collect($this->testInfo)
                ->reject(fn($item) => filled($item))
                ->isEmpty() && $this->validateTestName()->passes();
    }

    public function checkValidTestName(): array
    {
        try {
            if(!empty($this->testInfo['name']))
                $this->validateTestName()->validate();

            return ['success' => true];

        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message'  => $e->getMessage()
            ];
        }
    }
}