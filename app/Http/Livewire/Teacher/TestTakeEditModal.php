<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use tcCore\Http\Helpers\Choices\ChildChoice;
use tcCore\Http\Helpers\Choices\ParentChoice;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Livewire\Teacher\TestTake\Planned;
use tcCore\Http\Traits\Modal\WithPlanningFeatures;
use tcCore\Lib\TestParticipant\Factory as ParticipantFactory;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\Test;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTake;

class TestTakeEditModal extends TCModalComponent
{
    use WithPlanningFeatures;

    public TestTake $testTake;
    protected Test $test;
    public string $testName;
    public string $timeStart = '';
    public ?string $timeEnd = null;
    public $allowedPeriods;
    public $allowedInvigilators = [];
    public $allowedTeachers = [];
    public $selectedInvigilators = [];

    public bool $allowedToEnableMrChadd;

    protected function validationAttributes(): array
    {
        return [
            'testTake.weight' => str(__('teacher.Weging'))->lower(),
            'testTake.allow_inbrowser_testing' => __('teacher.Browsertoetsen toestaan'),
            'testTake.guest_accounts' => __('teacher.Test-Direct toestaan'),
            'testTake.enable_mr_chadd' => __('teacher.enable_mr_chadd'),
        ];
    }

    public function mount(TestTake $testTake)
    {
        if (!$testTake->isAllowedToView(auth()->user())) {
            $this->closeModal();
        }

        $this->testTake = $testTake;
        $this->test = $testTake->test;
        $this->testName = $testTake->test->name;
        $this->timeStart = $testTake->time_start;
        $this->timeEnd = $testTake->time_end;

        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();
        $this->allowedInvigilators = $this->getAllowedInvigilators();
        $this->allowedTeachers = $this->getAllowedTeachers();
        $this->selectedInvigilators = $this->testTake->invigilatorUsers()->pluck('id');

        $this->allowedToEnableMrChadd = ($this->testTake->schoolLocation->allow_mr_chadd && $this->test->isAssignment());
    }

    public function booted()
    {
        $this->test = $this->testTake->test;
    }

    public function render()
    {
        return view('livewire.teacher.test-take-edit-modal');
    }

    private function getConditionalRules(): array
    {
        $conditionalRules = [];
        if ($this->rttiExportAllowed) {
            $conditionalRules['testTake.is_rtti_test_take'] = 'required';
        }
        if (empty($this->classesAndStudents['children'])) {
            $conditionalRules['testTake.guest_accounts'] = 'accepted';
        }
        $conditionalRules['testTake.invigilator_note'] = 'sometimes';
        $conditionalRules['testTake.period_id'] = 'sometimes';
        if ($this->testTake->test->test_kind_id === TestKind::ASSIGNMENT_TYPE) {
            $conditionalRules['testTake.enable_mr_chadd'] = 'required';
        }
        return $conditionalRules;
    }

    protected function getMessages()
    {
        return [
            'testTake.guest_accounts.accepted' => __('validation.school_class_or_guest_accounts_required')
        ];
    }

    public function getSchoolClassesProperty(): array
    {
        $classes = SchoolClass::filtered(['user_id' => auth()->id(), 'current' => true])
            ->with([
                'studentUsers:id,name,name_first,name_suffix,uuid',
                'studentUsers.roles'
            ])
            ->get();
        $participantUserUuids = $this->testTake
            ->load([
                'testParticipants:id,test_take_id,user_id,school_class_id',
                'testParticipants.user:id,uuid',
                'testParticipants.user.roles'
            ])
            ->testParticipants
            ->mapWithKeys(fn($participant) => [$participant->user->uuid => $participant->school_class_id]);

        return $this->buildChoicesArrayWithClasses(
            $classes,
            fn($studentUser, $class) => $participantUserUuids->get($studentUser->uuid) === $class->id
        );
    }

    public function save(): void
    {
        $this->prepareTestTakeForValidation();
        $this->validate();

        $this->handleParticipants();
        $this->handleInvigilators();
        $this->testTake->save();

        $this->dispatchBrowserEvent('notify', ['message' => __('cms.Wijzigingen opgeslagen')]);
        $this->emitTo(Planned::class, 'refresh');
        $this->closeModal();
    }

    /**
     * @return void
     */
    private function handleParticipants(): void
    {
        ParticipantFactory::generateForUsers($this->testTake, $this->classesAndStudents);
    }

    private function prepareTestTakeForValidation(): void
    {
        /* TODO: Need to add 2 hours because of casting issues, u ugly */
        /* No need to add the extra hours, as long as the time start is parsed again with the current timezone */
        $this->testTake->time_start = Carbon::parse($this->timeStart);
        if ($this->timeEnd) {
            $this->testTake->time_end = Carbon::parse($this->timeEnd);
        }
    }

    private function handleInvigilators(): void
    {
        $this->testTake->saveInvigilators($this->selectedInvigilators);
        unset($this->testTake->invigilators);
    }
}
