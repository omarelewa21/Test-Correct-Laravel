<?php

namespace tcCore\Http\Livewire\Actions;

use Livewire\Component;
use tcCore\Test;

class TestPlanTest extends Component
{

    public $uuid;
    public $variant;
    public string $class;

    public function mount($uuid, $variant='icon-button', $class = '')
    {
        $this->uuid = $uuid;
        $this->variant = $variant;
        $this->class = $class;
    }

    public function render()
    {
        return view('livewire.actions.test-plan-test');
    }

    public function planTest()
    {
        $test = Test::findByUuid($this->uuid);
        if (!$test->hasDuplicateQuestions() && !$test->hasToFewQuestionsInCarousel() && !$test->hasNotEqualScoresForSubQuestionsInCarousel()) {
            $this->emit('openModal', 'teacher.planning-modal', ['testUuid' => $this->uuid]);
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
