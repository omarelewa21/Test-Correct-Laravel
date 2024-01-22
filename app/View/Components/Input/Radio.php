<?php

namespace tcCore\View\Components\Input;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Radio extends Component
{

    public function __construct(
        public mixed  $value,
        public string  $name,
        public bool    $checked = false,
        public bool    $disabled = false,
        public ?string $textRight = null,
        public ?string $textLeft = null,
        public ?string $labelClasses = null,
    ) {}

    public function render(): View
    {
        return view('components.input.radio');
    }
}
