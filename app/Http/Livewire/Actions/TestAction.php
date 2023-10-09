<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Test;

abstract class TestAction extends TCComponent
{
    public $uuid;
    public $class;
    public $variant;
    public bool $disabled;
    protected $test;

    protected $listeners = ['test-updated' => 'testUpdated'];

    public function mount($uuid, $variant, $class): void
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

    public function testUpdated()
    {
        $this->mount($this->uuid, $this->variant, $this->class);
    }

    public abstract function handle();
    protected abstract function getDisabledValue();

    protected function isInCms(): bool
    {
        return Str::of(Livewire::originalUrl())->contains('question-editor');
    }
}