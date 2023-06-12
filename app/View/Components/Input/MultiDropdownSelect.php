<?php

namespace tcCore\View\Components\Input;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MultiDropdownSelect extends Component
{

    public function __construct(
        public string $title,
        public iterable $options,
        public ?string $containerId = 'multi-select'
    ) {}

    public function render(): View
    {
        return view('components.input.multi-dropdown-select');
    }
}
