<?php

namespace tcCore\View\Components\Partials;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NecklaceNavigation extends Component
{

    public function __construct(public int $position) {}

    public function render(): View
    {
        return view('components.partials.necklace-navigation');
    }
}
