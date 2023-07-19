<?php

namespace tcCore\View\Components\Input;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MultiDropdownSelect extends Component
{

    public function __construct(
        public string   $title,
        public iterable $options,
        public ?string  $containerId = 'multi-select',
        public ?string  $label = null,
        public ?array   $itemLabels = null,
    ) {
        $this->setItemLabels();
    }

    public function render(): View
    {
        return view('components.input.multi-dropdown-select');
    }

    private function setItemLabels()
    {
        $defaults = [
            'parent_disabled'    => __('general.unavailable'),
            'child_disabled'     => __('general.unavailable'),
            'placeholder_open'   => __('cms.Search...'),
            'placeholder_closed' => __('test-take.Selecteer...'),
        ];
        $this->itemLabels = array_merge($defaults, $this->itemLabels ?? []);
    }
}
