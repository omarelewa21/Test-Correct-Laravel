<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class QuestionCard extends Component
{
    public $question;

    public function __construct($question)
    {
        $this->question = $question;
    }

    public function render(): string
    {
        return 'components.grid.question-card';
    }
}
