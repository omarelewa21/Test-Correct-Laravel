<?php

namespace tcCore\View\Components\input;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ColorPicker extends Component
{
    public function __construct(public string $name)
    {
    }

    public function render(): View
    {
        return view('components.input.color-picker');
    }

    public function isStrokeColorInput(): bool
    {
        return str_contains($this->name, 'stroke-color');
    }

    public function isPenColorInput(): bool
    {
        return str_contains($this->name, 'pen-color');
    }
}
