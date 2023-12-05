<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;
use tcCore\GroupQuestion;
use tcCore\TestQuestion;

class GroupQuestionContainer extends Component
{
    public array $error = [];

    public function __construct(
        public TestQuestion      $testQuestion,
        public GroupQuestion $question,
        public bool              $double,
    ) {
        $this->setErrors();
    }

    public function render(): string
    {
        return 'components.sidebar.cms.group-question-container';
    }

    private function setErrors(): void
    {
        $this->error = $this->question->getConstructorErrors($this->double);
    }

}
