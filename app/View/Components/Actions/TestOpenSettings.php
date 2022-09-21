<?php

namespace tcCore\View\Components\Actions;

class TestOpenSettings extends TestActionComponent
{
    public function __construct($uuid, $variant = 'icon-button')
    {
        parent::__construct($uuid, $variant);
    }

    protected function getDisabledValue(): bool
    {
        return !$this->test->canEdit(auth()->user()) || auth()->user()->isValidExamCoordinator();
    }
}
