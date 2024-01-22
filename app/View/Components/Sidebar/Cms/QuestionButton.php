<?php

namespace tcCore\View\Components\Sidebar\Cms;

use Illuminate\View\Component;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\TestQuestion;

class QuestionButton extends Component
{
    public bool $active = false;

    public function __construct(
        public QuestionInterface $question,
        public int               $loop,
        public TestQuestion      $testQuestion,
        public bool              $double,
        ?string                  $activeTestQuestionUuid,
        ?string                  $activeGQQUuid,
        public bool              $subQuestion = false,
    ) {
        $this->active = $this->isActiveCheck($activeTestQuestionUuid, $activeGQQUuid);
    }

    public function render(): string
    {
        return 'components.sidebar.cms.question-button';
    }

    private function isActiveCheck(string $activeTestQuestionUuid, string $activeGQQUuid): bool
    {
        if ($activeTestQuestionUuid !== $this->testQuestion->uuid) {
            return false;
        }

        return !$this->subQuestion || $activeGQQUuid === $this->question->groupQuestionQuestionUuid;
    }
}
