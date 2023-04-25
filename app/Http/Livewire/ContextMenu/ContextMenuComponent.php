<?php

namespace tcCore\Http\Livewire\ContextMenu;

use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Livewire\TCComponent;

abstract class ContextMenuComponent extends TCComponent
{
    public function render()
    {
        $templateName = Str::kebab(class_basename(get_called_class()));
        return view('livewire.context-menu.' . $templateName);
    }

    public abstract function setContextValues($uuid, $contextData): bool;

    public function isInCms(): bool
    {
        return Str::of(Livewire::originalUrl())->contains('question-editor');
    }
}