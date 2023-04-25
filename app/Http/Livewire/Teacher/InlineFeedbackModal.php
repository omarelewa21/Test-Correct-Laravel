<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Answer;
use tcCore\Http\Livewire\TCModalComponent;

class InlineFeedbackModal extends TCModalComponent
{
    public $answer;
    public $feedback = '';
    public $editorId;

    public function mount(Answer $answer)
    {
        $this->answer = $answer;
        $this->editorId = 'feedback-' . $this->answer->uuid;

        $this->setFeedbackProperty($answer);
    }

    public function render()
    {
        return view('livewire.teacher.inline-feedback-modal');
    }

    public static function modalMaxWidthClass(): string
    {
        return 'modal-full-screen';
    }

    public function updatedFeedback(): void
    {
        $this->answer
            ->feedback()
            ->updateOrCreate(
                ['user_id' => auth()->id()],
                ['message' => clean($this->feedback)]
            );
        $this->emit('inline-feedback-saved');
    }

    /**
     * @param Answer $answer
     * @return void
     */
    private function setFeedbackProperty(Answer $answer): void
    {
        $feedbackRecord = $answer->feedback()->where('user_id', auth()->id())->first();

        if ($feedbackRecord) {
            $this->feedback = $feedbackRecord->message ?? '';
            return;
        }
        $this->feedback = json_decode($answer->json)?->value ?? '';
    }
}
