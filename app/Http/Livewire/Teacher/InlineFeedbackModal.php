<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Answer;
use tcCore\Http\Livewire\TCModalComponent;

class InlineFeedbackModal extends TCModalComponent
{
    public $answer;
    public bool $disabled = false;
    public string $feedback = '';
    public string $editorId = 'feedback-';

    public function mount(Answer $answer, bool $disabled = false)
    {
        $this->answer = $answer;
        $this->disabled = $disabled;
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
        $feedbackRecord = $answer->feedback()->first();

        if ($feedbackRecord) {
            $this->feedback = $feedbackRecord->message ?? '';
            return;
        }
        $this->feedback = json_decode($answer->json)?->value ?? '';
    }
}
