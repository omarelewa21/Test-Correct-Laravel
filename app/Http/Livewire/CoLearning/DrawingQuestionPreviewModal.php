<?php

namespace tcCore\Http\Livewire\CoLearning;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Storage;
use LivewireUI\Modal\ModalComponent;
use tcCore\Answer;

class DrawingQuestionPreviewModal extends ModalComponent
{
    public string $imgSrc;
    protected static array $maxWidths = [
        'full' => 'modal-full-screen',
    ];

    public function mount($imgSrc)
    {
        $this->imgSrc = $imgSrc;
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
