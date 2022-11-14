<?php

namespace tcCore\Http\Livewire\CoLearning;

use LivewireUI\Modal\ModalComponent;

class DrawingQuestionPreviewModal extends ModalComponent
{

    protected static array $maxWidths = [
        'full' => 'modal-full-screen',
    ];

    public function mount($src = null)
    {
        $this->imgSrc = $src;
        //link to image? or answer?
    }

    public function render()
    {
        return view('livewire.co-learning.drawing-question-preview-modal');
    }


    /*
     * Modal settings
     */
    public static function modalMaxWidth(): string
    {
        return 'full';
    }

    public static function destroyOnClose(): bool
    {
        return false;
    }
}
