<?php

namespace tcCore\Http\Livewire\Questions\Preview;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

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
    public $grid = '0';
    public $usesNewDrawingTool = false;

    public function mount()
    {
        $this->initPlayerInstance();

        $svgHelper = new SvgHelper($this->question->uuid);

        $this->question_svg = $svgHelper->getQuestionSvg($this->question);

        $this->grid_svg = $this->question->grid_svg;
        $this->grid = $this->question->grid;
        $this->usesNewDrawingTool = Auth::user()->schoolLocation()->value('allow_new_drawing_question');

        $this->backgroundImage = $this->question->getBackgroundImage();
    }

    public function updatedAnswer($value)
    {

        $this->drawingModalOpened = false;

    }

    public function render()
    {
        return view('livewire.questions.preview.drawing-question');
    }


    private function initPlayerInstance()
    {
        $this->playerInstance = 'eppi_' . rand(1000, 9999999);
    }

    public function handleUpdateDrawingData()
    {

    }
}
