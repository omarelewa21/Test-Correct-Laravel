<?php

namespace tcCore\Http\Traits;

use tcCore\Test;

trait WithTestPlanning
{
    public function planTestWithUuid($testUuid)
    {
        $test = Test::findByUuid($testUuid);
        if ($test->meetsQuestionRequirementsForPlanning()) {
            $this->emit('openModal', 'teacher.planning-modal', ['testUuid' => $test->uuid]);
            return false;
        }
        $primaryAction = false;
        $message = __('modal.cannot_schedule_test_full_not_author');

        if ($test->author->is(auth()->user())) {
            $primaryAction = route('teacher.question-editor',
                [
                    'action'         => 'add',
                    'owner'          => 'test',
                    'testId'         => $test->uuid,
                    'testQuestionId' => '',
                    'type'           => '',
                    'isCloneRequest' => '',
                    'withDrawer'     => 'true',
                ]
            );
            $message = __('modal.cannot_schedule_test_full_author');
        }

//        $mode = [
//            'hasDuplicateQuestions' => $test->hasDuplicateQuestions() ,
//            'hasToFewQuestionsInCarousel' => $test->hasToFewQuestionsInCarousel(),
//            'hasEqualScoreForSubQuestions' => $test->hasEqualScoresForSubQuestions(),
//        ];
//
//        $message = $message . print_r($mode, true);

        $this->emit(
            'openModal',
            'alert-modal', [
            'message'               => $message,
            'title'                 => __('modal.cannot_schedule_test'),
            'primaryAction'         => $primaryAction,
            'primaryActionBtnLabel' => __('modal.Toets bewerken')
        ]);
    }

}