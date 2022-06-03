<?php

namespace tcCore\View\Components\Sidebar\Cms;

class DummyQuestionButton extends AbstractDummyQuestionButton
{
    public function __construct($loop)
    {
        Parent::__construct($loop);
    }

    public function render(): string
    {
        return 'components.sidebar.cms.dummy-question-button';
    }
}
