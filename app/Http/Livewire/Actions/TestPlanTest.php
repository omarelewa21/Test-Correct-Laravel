<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use tcCore\Test;

class TestPlanTest extends Component
{

    public $uuid;
    public $variant;
    public string $class;

    public function mount($uuid, $variant = 'icon-button-with-text', $class = '')
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
        if ($test->meetsQuestionRequirementsForPlanning()) {
            $this->emit('openModal', 'teacher.planning-modal', ['testUuid' => $this->uuid]);
            return false;
        }
        $primaryAction = false;
        $message = __('modal.cannot_schedule_test_full_not_author');

        if ($this->isInCms()) {
            $this->emitToAlertModal(__('modal.cannot_schedule_test_full_author'), false);
            return true;
        }
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

    private function isInCms(): bool
    {
        return Str::of(Livewire::originalUrl())->contains('question-editor');
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
