<?php

namespace tcCore\View\Components\Actions;

use Auth;
use tcCore\Test;

class TestOpenPreview extends TestActionComponent
{
    public $url;

    public function __construct($uuid, $variant = 'icon-button')
    {
        parent::__construct($uuid, $variant);

        $this->url = route('teacher.test-preview', ['test' => $uuid]);
    }

    protected function getDisabledValue(): bool
    {
        return Auth::user()->isValidExamCoordinator();
    }
}
