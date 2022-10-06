<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use tcCore\Test;

abstract class TestAction extends Component
{
    public $uuid;
    public $class;
    public $variant;
    public bool $disabled;
    protected $test;

    public function mount($uuid, $variant, $class)
    {
        $this->test = Test::findByUuid($uuid);
        $this->uuid = $uuid;
        $this->class = $class;
        $this->variant = $variant;
        $this->disabled = $this->getDisabledValue();
    }

    public function render()
    {
        $templateName = Str::kebab(class_basename(get_called_class()));
        return view('livewire.actions.'.$templateName);
    }

    public abstract function handle();
    protected abstract function getDisabledValue();

    protected function isInCms(): bool
    {
        return Str::of(Livewire::originalUrl())->contains('question-editor');
    }
}