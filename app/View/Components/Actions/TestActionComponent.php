<?php

namespace tcCore\View\Components\Actions;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\Test;

abstract class TestActionComponent extends Component
{
    public $test;
    public $variant;
    public $disabled = false;

    public function __construct($uuid, $variant='icon-button')
    {
        $this->test = Test::findByUuid($uuid);
        $this->variant = $variant;
        $this->disabled = $this->getDisabledValue();
    }

    public function render()
    {
        $templateName = Str::kebab(class_basename(get_called_class()));
        return view('components.actions.'.$templateName);
    }

    protected abstract function getDisabledValue();
}