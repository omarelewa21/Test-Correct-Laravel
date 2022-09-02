<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;

abstract class TestAction extends Component
{
    public $uuid;
    public $class;
    public $variant;

    public function mount($uuid, $variant = 'icon-button', $class)
    {
        $this->uuid = $uuid;
        $this->class = $class;
        $this->variant = $variant;
    }

    public function render()
    {
        $templateName = Str::kebab(class_basename(get_called_class()));
        return view('livewire.actions.'.$templateName);
    }

    protected function isInCms(): bool
    {
        return Str::of(Livewire::originalUrl())->contains('question-editor');
    }
}