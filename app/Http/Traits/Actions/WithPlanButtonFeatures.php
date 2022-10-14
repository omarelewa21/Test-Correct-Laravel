<?php

namespace tcCore\Http\Traits\Actions;

use tcCore\Test;

trait WithPlanButtonFeatures
{
    private function planTest()
    {
        $test = Test::findByUuid($this->uuid);
        $questionCount = $test->getQuestionCount();
        if ($test->meetsQuestionRequirementsForPlanning() && $questionCount > 0) {
            $this->emit('openModal', $this->modalName, ['testUuid' => $this->uuid]);
            return false;
        }

        if (!$questionCount) {
            $this->emitToAlertModal(__('modal.cannot_schedule_no_questions'), false);
            return true;
        }
        if ($this->isInCms()) {
            $this->emitToAlertModal(__('modal.cannot_schedule_test_full_author'), false);
            return true;
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

        $this->emitToAlertModal($message, $primaryAction);
    }

    /**
     * @param $message
     * @param $primaryAction
     * @return void
     */
    private function emitToAlertModal($message, $primaryAction): void
    {
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