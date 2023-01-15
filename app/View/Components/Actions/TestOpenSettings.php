<?php

namespace tcCore\View\Components\Actions;

use Auth;

class TestOpenSettings extends TestActionComponent
{
    public string $modalName;
    public function __construct($uuid, $variant = 'icon-button')
    {
        parent::__construct($uuid, $variant);
        $this->setModalName();
    }

    protected function getDisabledValue(): bool
    {
        return !$this->test->canEdit(auth()->user()) || auth()->user()->isValidExamCoordinator();
    }

    private function setModalName()
    {
        $this->modalName = (Auth::user()->isToetsenbakker() ? 'toetsenbakker' : 'teacher') . '.test-edit-modal';
    }
}
