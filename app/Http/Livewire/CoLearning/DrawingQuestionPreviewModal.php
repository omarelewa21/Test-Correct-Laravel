<?php

namespace tcCore\Http\Livewire\CoLearning;

use tcCore\Http\Livewire\Modal\Preview;

class DrawingQuestionPreviewModal extends Preview
{
    public string $imgSrc;

    public function mount()
    {
        if($this->title === 'answer-model') {
            $this->title = __('co-learning.answer_model_drawing');
        }
        if($this->title === 'answer') {
            $this->title = __('co-learning.answer_drawing');
        }
    }

    public function render()
    {
        return view('livewire.co-learning.drawing-question-preview-modal');
    }
}
