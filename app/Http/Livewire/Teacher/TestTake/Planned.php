<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\AnonymousComponent;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\Choices\ChildChoice;
use tcCore\Http\Helpers\Choices\ParentChoice;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\SchoolClass;
use tcCore\TestTake as TestTakeModel;

class Planned extends TestTakeComponent
{
    public $dropdownData = [];
    public $selected = [];

    public function mount(TestTakeModel $testTake)
    {
        parent::mount($testTake);
        $this->dropdownData();
    }

    public function redirectToOverview()
    {
        return CakeRedirectHelper::redirectToCake('planned.my_tests');
    }

    public function fillGridData()
    {
        $this->testTake->load([
            'test:id,name,uuid,subject_id',
            'test.subject:id,name',
            'scheduledByUser:id,name,name_first,name_suffix',
            'user:id,name,name_first,name_suffix',
            'invigilatorUsers:id,name,name_first,name_suffix',

        ]);
        $schoolClasses = $this->testTake->schoolClasses()->get('name');
        $this->gridData = [
            [
                'title' => __('student.subject'),
                'data'  => $this->testTake->test->subject->name,
            ],
            [
                'title' => __('test-take.Afname gepland op'),
                'data'  => $this->testTake->time_start->format('d-m-Y'),
            ],
            [
                'title' => __('test-take.Gepland door'),
                'data'  => $this->testTake->scheduledByUser->getFullNameWithAbbreviatedFirstName(),
            ],
            [
                'title' => trans_choice('test-take.Klas', $schoolClasses->count()),
                'data'  => $schoolClasses
                    ->map(fn($class) => $class->name)
                    ->join(', ', sprintf(" %s ", __('test-take.and'))),
            ],
            [
                'title' => __('general.Docent'),
                'data'  => $this->testTake->user->getFullNameWithAbbreviatedFirstName(),
            ],
            [
                'title' => trans_choice('test-take.Surveillant', $this->testTake->invigilatorUsers->count()),
                'data'  => $this->testTake->invigilatorUsers
                    ->map(fn($user) => $user->getFullNameWithAbbreviatedFirstName())
                    ->join(', ', sprintf(" %s ", __('test-take.and'))),
            ],
            [
                'title' => __('teacher.Weging'),
                'data'  => $this->testTake->weight,
            ],
            [
                'title' => __('teacher.type'),
                'data'  => Blade::renderComponent(
                    new AnonymousComponent(
                        'components.partials.test-take-type-label',
                        ['type' => $this->testTake->retake]
                    )
                ),
            ],
        ];
    }

    protected function setStudentData(): void
    {
        $this->testTake->load([
            'testParticipants',
            'testParticipants.user:id,name,name_first,name_suffix,uuid'
        ]);

        $this->participants = $this->testTake
            ->testParticipants
            ->each(function ($participant) {
                $participant->name = $participant->user->name_full;
                $participant->present = $this->activeParticipantUuids->contains($participant->user->uuid);
            });
    }

    public function dropdowndata()
    {
        $classes = SchoolClass::filtered(['user_id' => auth()->id(), 'current' => true])->get();
        $this->dropdownData = $classes->map(function ($class) {
            return ParentChoice::build(
                value           : $class->uuid,
                label           : $class->name,
                customProperties: ['parentId' => $class->uuid],
                children        : $class->studentUsers->map(function ($student) use ($class) {
                    return ChildChoice::build(
                        value           : $student->uuid,
                        label           : $student->name_full,
                        customProperties: [
                            'parentId'    => $class->uuid,
                            'parentLabel' => $class->name,
                        ]
                    );
                })
            );
        })->toArray();
    }
}