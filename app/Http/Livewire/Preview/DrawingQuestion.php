<?php

namespace tcCore\Http\Livewire\Preview;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Traits\WithNotepad;

class DrawingQuestion extends Component
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $question;
    public $testId;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;

    public $additionalText;

    public $playerInstance;

    public $backgroundImage;

    public $answer_svg = null;
    public $question_svg = null;
    public $grid_svg = '0.00';
    public $usesNewDrawingTool = false;

    public function mount()
    {
        $this->initPlayerInstance();

        $svgHelper = new SvgHelper($this->question->uuid);

        $this->question_svg = $svgHelper->getQuestionSvg($this->question);

        $this->grid_svg = $this->question->grid_svg;
        $this->usesNewDrawingTool = Auth::user()->schoolLocation()->value('allow_new_drawing_question') && (blank($this->question->bg_name) && empty($this->question->grid));

        $this->backgroundImage = $this->question->getBackgroundImage();
    }

    public function updatedAnswer($value)
    {

        $this->drawingModalOpened = false;

    }

    public function render()
    {
        return view('livewire.preview.drawing-question');
    }


    private function initPlayerInstance()
    {
        $this->playerInstance = 'eppi_' . rand(1000, 9999999);
    }

    public function handleUpdateDrawingData()
    {

    }
}
