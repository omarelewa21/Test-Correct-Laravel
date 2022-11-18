<?php

namespace tcCore\Http\Livewire\CoLearning;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Storage;
use LivewireUI\Modal\ModalComponent;
use tcCore\Answer;

class DrawingQuestionPreviewModal extends ModalComponent
{
    public string $src;
    public $answer;

    protected static array $maxWidths = [
        'full' => 'modal-full-screen',
    ];

    public function mount($answerId)
    {
        $this->answer = Answer::find($answerId);

        if(!$this->answer || !$this->answer->isAnswered){
            $this->closeModal();
            return;
        }
        try {
            if($this->handleDrawingQuestionWithPngExtension()){
                return true;
            }
            $this->handleDrawingQuestionWithoutPngExtension();
        }catch (\Exception $e){
            throw $e;
            Bugsnag::notifyException($e);
            $this->imgSrc = '';
        }
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


    private function handleDrawingQuestionWithPngExtension() // new Drawing question
    {
        try {
            $png = Storage::get($this->answer->getDrawingStoragePathPng());
            $this->imgSrc = "data:image/png;base64," . base64_encode($png);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function handleDrawingQuestionWithoutPngExtension() // old Drawing question
    {
        $file = Storage::get($this->answer->getDrawingStoragePath());
        if (substr($file, 0, 4) === '<svg') {
            throw new \Exception(sprintf('answer of old drawing question with id:%d has svg as drawingStoragePath',$this->answer->getKey()));
        } else {
            $this->imgSrc = "data:image/png;base64," . base64_encode(file_get_contents($file));
        }
    }
}
