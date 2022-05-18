<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;

class QuestionOptions extends Component
{
    public $testQuestion;
    public $question;
    public $subQuestion;

    public function __construct($testQuestion, $question, $subQuestion)
    {
        $this->testQuestion = $testQuestion;
        $this->question = $question;
        $this->subQuestion = $subQuestion;
    }

    public function render(): string
    {
        return 'components.sidebar.cms.question-options';
    }
}
