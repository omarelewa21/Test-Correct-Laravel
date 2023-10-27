<?php

namespace tcCore\View\Components\Button;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CollapseChevron extends Component
{
    public function __construct(
        public bool    $disabled = false,
        public ?string $handler = 'expanded'
    ) {}

    public function render(): View
    {
        return view('components.button.collapse-chevron');
    }
}
