<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\View;

class QuestionCardDetail extends QuestionCardBase
{
    public $testQuestion;

    public function __construct($testQuestion, $mode = 'page')
    {
        $this->testQuestion = $testQuestion;
        parent::__construct($testQuestion->question, $testQuestion->order);
    }

    public function render(): View
    {
        return view('components.grid.question-card-detail');
    }
}
