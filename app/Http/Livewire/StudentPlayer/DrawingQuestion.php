<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use tcCore\Answer;
use tcCore\DrawingQuestion as DrawingQuestionModel;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;

abstract class DrawingQuestion extends TCComponent
{
    use withCloseable;

    public $question;
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
        $this->backgroundImage = $this->question->getBackgroundImage();
        $this->usesNewDrawingTool = Auth::user()->schoolLocation()->value('allow_new_drawing_question');
    }

    private function initPlayerInstance()
    {
        $this->playerInstance = 'eppi_' . rand(1000, 9999999);
    }
}
