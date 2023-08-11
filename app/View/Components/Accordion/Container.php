<?php

namespace tcCore\View\Components\Accordion;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Container extends Component
{
    public function __construct(
        public $activeContainerKey = null
    ) {}

    public function render(): View
    {
        return view('components.accordion.container');
    }
}
