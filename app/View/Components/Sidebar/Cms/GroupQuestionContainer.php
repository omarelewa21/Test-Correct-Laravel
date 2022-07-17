<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;

class GroupQuestionContainer extends Component
{

    public $testQuestion;
    public $question;
    public $double;

    public function __construct($testQuestion, $question, $double)
    {
        $this->testQuestion = $testQuestion;
        $this->question = $question;
        $this->double = $double;
    }

    public function render(): string
    {
        return 'components.sidebar.cms.group-question-container';
    }
}
