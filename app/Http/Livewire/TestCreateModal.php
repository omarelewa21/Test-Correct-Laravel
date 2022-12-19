<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use tcCore\Period;
use tcCore\Test;

abstract class TestCreateModal extends TestModal
{
    protected function setRequestPropertyDefaults(): void
    {
        $this->request = [
            'name'                 => '',
            'abbreviation'         => '',
            'test_kind_id'         => 3,
            'subject_id'           => $this->allowedSubjects->first()->id,
            'education_level_id'   => $this->allowedEductionLevels->first()->id,
            'education_level_year' => 1,
            'period_id'            => Period::filtered(['current_school_year' => true])->first()->id ?? $this->allowedPeriods->first()->id,
            'shuffle'              => 0,
            'introduction'         => '',
        ];
    }

    protected function performModalAction(): Test
    {
        return $this->createTestFromRequest();
    }

    protected function finishSubmitting(Test $test): void
    {
        redirect(
            route('teacher.question-editor',
                [
                    'action'         => 'add',
                    'owner'          => 'test',
                    'testId'         => $test->uuid,
                    'testQuestionId' => '',
                    'type'           => '',
                    'isCloneRequest' => '',
                    'withDrawer'     => 'true',
                    'referrer'       => 'teacher.tests',
                ]
            )
        );

        $this->dispatchBrowserEvent('notify', ['message' => __('teacher.test created')]);
    }

    /**
     * @return Test
     */
    protected function createTestFromRequest(): Test
    {
        $test = new Test($this->request);
        $test->setAttribute('author_id', Auth::id());
        $test->setAttribute('owner_id', Auth::user()->school_location_id);
        $test->save();
        return $test;
    }
}