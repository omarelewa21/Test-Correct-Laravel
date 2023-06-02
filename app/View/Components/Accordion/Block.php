<?php

namespace tcCore\View\Components\Accordion;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Block extends Component
{
    public function __construct(
        public int|string $key,
        public bool       $disabled = false,
        public bool       $emitWhenSet = false,
        public bool       $upload = false,
        public string     $uploadModel = '',
        public array      $uploadRules = [],
        public ?string    $coloredBorderClass = null,
        public string     $mode = 'panel',
    ) {}

    public function render(): View
    {
        return view('components.accordion.block');
    }
}
