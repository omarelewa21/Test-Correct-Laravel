<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class QuestionCardDetail extends Component
{
    public $question;
    public $testQuestion;

    public function __construct($testQuestion)
    {
        $this->testQuestion = $testQuestion;
        $this->question = $testQuestion->question;
    }

    public function render(): string
    {
        return 'components.grid.question-card-detail';
    }
}
