<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\AnonymousComponent;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;

class Planned extends TestTakeComponent
{
    public function redirectToOverview()
    {
        return CakeRedirectHelper::redirectToCake('planned.my_tests');
    }

    public function fillGridData()
    {
        $this->testTake->load([
            'test:id,name,subject_id',
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
                'data'  => $this->testTake->time_start->format('h-m-Y'),
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
}