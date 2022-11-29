<?php

namespace tcCore\View\Components\Accordion;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Block extends Component
{
    public function __construct(public int $key)
    {
    }

    public function render(): View
    {
        return view('components.accordion.block');
    }
}
