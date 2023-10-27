<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\SvgHelper;

abstract class DrawingQuestion extends StudentPlayerQuestion
{
    public $drawingModalOpened = false;
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
