<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use tcCore\Http\Helpers\Choices\ChildChoice;
use tcCore\Http\Helpers\Choices\ParentChoice;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Traits\Modal\WithPlanningFeatures;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;

class TestTakeEditModal extends TCModalComponent
{
    use WithPlanningFeatures;

    public TestTake $testTake;
    protected Test $test;
    public string $testName;
    public $allowedPeriods;
    public $allowedInvigilators = [];
    public $allowedTeachers = [];
    public $selectedSchoolClasses = [];
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

        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();
        $this->allowedInvigilators = $this->getAllowedInvigilators();
        $this->allowedTeachers = $this->getAllowedTeachers();
    }

    public function booted()
    {
        $this->test = $this->testTake->test;
    }

    public function render()
    {
        return view('livewire.teacher.test-take-edit-modal');
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

    public function save()
    {
        $selectedUsers = $this->getSelectedUserIds();
        $existingParticipants = $this->testTake->testParticipants()->pluck('user_id');

//        $upsertData = $selectedUsers->diff($existingParticipants)->map(function ($userId) {
//            return
//                [
//                    'user_id' => $userId,
//                    'test_take_status_id' => TestTakeStatus::STATUS_PLANNED,
//
//                ];
//        });
//
//
//        $this->testTake->testParticipants->upsert(, ['test_take_id', 'user_id']);
    }

    private function getSelectedUserIds(): Collection
    {
        $schooClasses = SchoolClass::whereUuidIn(collect($this->classesAndStudents['children'])->pluck('parent'))
            ->get(['id', 'uuid'])
            ->mapWithKeys(fn($class) => [$class->uuid => $class->id]);

        $userIdsByStudent = User::whereUuidIn(collect($this->classesAndStudents['children'])->pluck('value'))
            ->distinct()
            ->pluck('id');

        return $userIdsByClass->merge($userIdsByStudent)->unique();
    }
}
