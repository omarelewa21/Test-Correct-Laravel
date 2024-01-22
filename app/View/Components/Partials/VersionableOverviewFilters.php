<?php

namespace tcCore\View\Components\Partials;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VersionableOverviewFilters extends Component
{

    public function __construct(
        public string $activeFilterContainer,
        public string $versionablePrefix,
        public string $searchProperty
    ) {}

    public function render(): View
    {
        return view('components.partials.versionable-overview-filters');
    }
}