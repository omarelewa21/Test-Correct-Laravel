<?php

namespace tcCore\View\Components\Partials;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OverviewContentSection extends Component
{
    public function __construct(
        public \Countable $results,
        public bool       $pagination,
        public int        $maxColumns = 3,
    ) {}

    public function render(): View
    {
        return view('components.partials.overview-content-section');
    }
}
