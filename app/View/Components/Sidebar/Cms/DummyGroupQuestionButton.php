<?php

namespace tcCore\View\Components\Sidebar\Cms;

class DummyGroupQuestionButton extends AbstractDummyQuestionButton
{
    public $testQuestionUuid;

    public function __construct($testQuestionUuid, $loop)
    {
        Parent::__construct($loop);
        $this->testQuestionUuid = $testQuestionUuid;
    }

    public function render(): string
    {
        return 'components.sidebar.cms.dummy-group-question-button';
    }
}
