<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\View;

class QuestionCardDetail extends QuestionCardBase
{
    public $testQuestion;

    public function __construct($testQuestion, $mode = 'page', $inTest = false)
    {
        $this->testQuestion = $testQuestion;
        parent::__construct($testQuestion->question, $testQuestion->order, false, $inTest);
    }

    public function render(): View
    {
        return view('components.grid.question-card-detail');
    }
}
