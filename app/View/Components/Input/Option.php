<?php

namespace tcCore\View\Components\Input;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;

class Option extends Component
{

    public function __construct(
        public string $label,
        public string $value = '',
    ) {}

    public function render(): View
    {
        return view('components.input.option');
    }
}
