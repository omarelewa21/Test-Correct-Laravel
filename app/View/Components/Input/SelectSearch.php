<?php

namespace tcCore\View\Components\Input;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectSearch extends Component
{
    public $options;
    public $placeholder;
    public $emptyOptionsMessage;
    public $name;
    public $level;
    public $disabled;

    public function __construct(
        $name,
        $placeholder = null,
        $emptyOptionsMessage = null,
        $level = null,
        $disabled = false)
    {
        $this->placeholder = $placeholder ?? __('Kies een waarde');
        $this->emptyOptionsMessage = $emptyOptionsMessage ?? __('Geen resultaat gevonden');
        $this->name = $name;
        $this->level = $level ?? 'top';
        $this->disabled = $disabled;
    }

    public function render(): View
    {
        return view('components.input.select-search');
    }
}
