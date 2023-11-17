<?php

namespace tcCore\View\Components;

use Illuminate\View\Component;

class Tooltip extends Component
{
    public function __construct(
        public $alwaysLeft = false,
        public $activeClasses = 'bg-primary text-white ',
        public $idleClasses = 'bg-system-secondary text-sysbase',
        public $idleIcon = '',
        public $iconWidth = null,
        public $iconHeight = null,
        public $tooltipClasses = '',
    ) {}

    public function render()
    {
        return view('components.tooltip');
    }
}
