<?php

namespace tcCore\Http\Livewire\TestPrint;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends Component
{
    use WithNotepad, WithCloseable, WithGroups;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;
    public $answered;

    public $additionalText;
    public $pngBase64;
    public $attachment_counters;

    public $oldDrawingQuestionGridHeight = null;
    public $oldDrawingQuestionGridWidth = null;

    public function mount()
    {
        $this->pngBase64 = $this->setDrawingQuestionBase64Image();
    }

    private function setDrawingQuestionBase64Image()
    {
        if (is_null($this->question['zoom_group'])) {
            if (!$this->question->getBackgroundImage() && $this->question->grid) {
                $this->setUpOldDrawingQuestionGrid();
            }
            return $this->question->getBackgroundImage();
        }
        $svgHelper = new SvgHelper($this->question['uuid']);
        return 'data:image/png;base64,' . base64_encode($svgHelper->getQuestionModelPNG());
    }

    private function setUpOldDrawingQuestionGrid()
    {
        $this->oldDrawingQuestionGridHeight = $this->question->grid * 1.5;
        if($this->question->grid <= 4) {
            $this->oldDrawingQuestionGridWidth = $this->question->grid * 2;
        }
        else {
            $this->oldDrawingQuestionGridWidth = ($this->question->grid * 2) + 1 ;
        }
    }

    public function render()
    {
        return view('livewire.test_print.drawing-question');
    }

}
