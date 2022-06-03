<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;

class GroupQuestionContainer extends Component
{

    public $testQuestion;
    public $question;

    public function __construct($testQuestion, $question)
    {
        $this->testQuestion = $testQuestion;
        $this->question = $question;
    }

    public function render(): string
    {
        return 'components.sidebar.cms.group-question-container';
    }
}
