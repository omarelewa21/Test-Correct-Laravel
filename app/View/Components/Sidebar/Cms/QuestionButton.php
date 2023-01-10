<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;

class QuestionButton extends Component
{
    public $question;
    public $loop;
    public $subQuestion;
    public $testQuestion;
    public $active = false;
    public $double;

    public function __construct($question, $loop, $subQuestion, $testQuestion, $activeTestQuestion, $activeGQQ, $double)
    {
        $this->question = $question;
        $this->loop = $loop;
        $this->subQuestion = $subQuestion;
        $this->testQuestion = $testQuestion;
        $this->double = $double;

        $this->active = $this->isActiveCheck($activeTestQuestion, $activeGQQ);
    }

    public function render(): string
    {
        return 'components.sidebar.cms.question-button';
    }

    private function isActiveCheck($activeTestQuestion, $activeGQQ): bool
    {
        if ($activeTestQuestion !== $this->testQuestion->uuid) {
            return false;
        }

        return !$this->subQuestion || $activeGQQ === $this->question->groupQuestionQuestionUuid;
    }
}
