<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use tcCore\Http\Controllers\FileManagementUsersController;
use tcCore\Http\Helpers\Choices\ChildChoice;
use tcCore\Http\Helpers\Choices\ParentChoice;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Livewire\Teacher\TestTake\Planned;
use tcCore\Http\Traits\Modal\WithPlanningFeatures;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;

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

    public $classesAndStudents = [
        'parents'  => [],
        'children' => []
    ];


    public function mount(TestTake $testTake)
    {
        $this->testTake = $testTake;
        $this->test = $testTake->test;
        $this->testName = $testTake->test->name;
        $this->timeStart = $testTake->time_start;
        $this->timeEnd = $testTake->time_end;

        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();
        $this->allowedInvigilators = $this->getAllowedInvigilators();
        $this->allowedTeachers = $this->getAllowedTeachers();
        $this->selectedInvigilators = $this->testTake->invigilatorUsers()->pluck('id');
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
        return $conditionalRules;
    }

    protected function getMessages() {
        return [
            'testTake.guest_accounts.accepted' => __('validation.school_class_or_guest_accounts_required')
        ];
    }

    public function getSchoolClassesProperty()
    {
        $classes = SchoolClass::filtered(['user_id' => auth()->id(), 'current' => true])->get();
        $participantUserUuids = $this->testTake
            ->load([
                'testParticipants:id,test_take_id,user_id,school_class_id',
                'testParticipants.user:id,uuid',
            ])
            ->testParticipants
            ->mapWithKeys(fn($participant) => [$participant->user->uuid => $participant->school_class_id]);

        return $classes->map(function ($class) use ($participantUserUuids) {
            return ParentChoice::build(
                value           : $class->uuid,
                label           : $class->name,
                customProperties: ['parentId' => $class->uuid],
                children        : $class->studentUsers->map(
                    function ($studentUser) use ($participantUserUuids, $class) {
                        return ChildChoice::build(
                            value           : $studentUser->uuid,
                            label           : $studentUser->name_full,
                            customProperties: [
                                'parentId'    => $class->uuid,
                                'parentLabel' => $class->name,
                                'selected'    => $participantUserUuids->get($studentUser->uuid) === $class->id
                            ]
                        );
                    }
                )
            );
        })->toArray();
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

    private function getParticipantProposals(): Collection
    {
        $selectedClasses = $this->getSelectedClasses();
        return $this->getSelectedUserIds()
            ->mapWithKeys(function ($userId, $userUuid) use ($selectedClasses) {
                $child = collect($this->classesAndStudents['children'])
                    ->first(fn($child) => $child['value'] === $userUuid);
                return [
                    $userId => [
                        'userId'  => $userId,
                        'classId' => $selectedClasses[$child['parent']]
                    ]
                ];
            });
    }

    private function getSelectedUserIds(): Collection
    {
        $userUuids = collect($this->classesAndStudents['children'])->pluck('value');
        if ($userUuids->isEmpty()) return collect();
        return User::whereUuidIn($userUuids)
            ->distinct()
            ->get(['id', 'uuid'])
            ->mapWithKeys(fn($user) => [$user->uuid => $user->id]);
    }

    private function getSelectedClasses(): Collection
    {
        $schoolClassUuids = collect($this->classesAndStudents['children'])->pluck('parent');
        if ($schoolClassUuids->isEmpty()) return collect();
        return SchoolClass::whereUuidIn($schoolClassUuids)
            ->get(['id', 'uuid'])
            ->mapWithKeys(fn($class) => [$class->uuid => $class->id]);
    }

    private function deleteParticipants(Collection $participantsToDelete): void
    {
        if ($participantsToDelete->isEmpty()) return;
        TestParticipant::whereIn('user_id', $participantsToDelete->pluck('user_id'))
            ->whereTestTakeId($this->testTake->id)
            ->delete();
//        TestParticipant::destroy($participantsToDelete->pluck('id'));
    }

    private function updateParticipants(Collection $participantsToUpdate, Collection $participantProposals): void
    {
        $participantsToUpdate->each(function ($participant) use ($participantProposals) {
            $participant->update(['school_class_id' => $participantProposals[$participant->user_id]['classId']]);
        });
    }

    private function createParticipants(Collection $participantsToCreate): void
    {
        $newParticipants = $participantsToCreate->map(function ($proposal) {
            return [
                'user_id'                 => $proposal['userId'],
                'school_class_id'         => $proposal['classId'],
                'test_take_status_id'     => TestTakeStatus::STATUS_PLANNED,
                'allow_inbrowser_testing' => $this->testTake->allow_inbrowser_testing,
            ];
        });

        $this->testTake->testParticipants()->createMany($newParticipants);
    }

    /**
     * @param Collection $participantProposals
     * @param mixed $existingParticipants
     * @return Collection
     */
    private function getParticipantsToCreate(Collection $participantProposals, mixed $existingParticipants): Collection
    {
        return $participantProposals->filter(function ($proposal) use ($existingParticipants) {
            return $existingParticipants->doesntContain(function ($participant) use ($proposal) {
                return $participant->user_id === $proposal['userId']
                    && $participant->school_class_id === $proposal['classId'];
            });
        });
    }

    /**
     * @param mixed $existingParticipants
     * @param Collection $participantProposals
     * @return mixed
     */
    private function getParticipantsToDelete(mixed $existingParticipants, Collection $participantProposals): mixed
    {
        return $existingParticipants->filter(function ($participant) use ($participantProposals) {
            return $participantProposals->doesntContain(function ($proposal) use ($participant) {
                return $participant->user_id === $proposal['userId']
                    && $participant->school_class_id === $proposal['classId'];
            });
        });
    }

    /**
     * @param mixed $participantsToDelete
     * @param Collection $participantsToCreate
     * @return mixed
     */
    private function getParticipantsToUpdate(mixed $participantsToDelete, Collection $participantsToCreate): mixed
    {
        return $participantsToDelete->filter(function ($participant) use ($participantsToCreate) {
            return $participantsToCreate->contains(fn($proposal) => $proposal['userId'] === $participant->user_id);
        })->each(function ($participant) use ($participantsToDelete, $participantsToCreate) {
            $participantsToCreate->forget(
                $participantsToCreate->search(
                    fn($participantToCreate) => $participantToCreate['userId'] === $participant->user_id
                )
            );
            $participantsToDelete->forget(
                $participantsToDelete->search(
                    fn($participantToDelete) => $participantToDelete->user_id === $participant->user_id
                )
            );
        });
    }

    /**
     * @return void
     */
    private function handleParticipants(): void
    {
        $participantProposals = $this->getParticipantProposals();
        $existingParticipants = $this->testTake->testParticipants;

        $participantsToCreate = $this->getParticipantsToCreate($participantProposals, $existingParticipants);
        $participantsToDelete = $this->getParticipantsToDelete($existingParticipants, $participantProposals);
        $participantsToUpdate = $this->getParticipantsToUpdate($participantsToDelete, $participantsToCreate);

        $this->createParticipants($participantsToCreate);
        $this->deleteParticipants($participantsToDelete);
        $this->updateParticipants($participantsToUpdate, $participantProposals);
    }

    private function prepareTestTakeForValidation(): void
    {
        /* TODO: Need to add 2 hours because of casting issues, u ugly */
        $this->testTake->time_start = Carbon::parse($this->timeStart)->addHours(2);
        $this->testTake->time_end = Carbon::parse($this->timeEnd)->addHours(2);
    }

    private function handleInvigilators()
    {
        $this->testTake->saveInvigilators($this->selectedInvigilators);
        unset($this->testTake->invigilators);
    }
}
