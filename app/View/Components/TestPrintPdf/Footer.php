<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class Footer extends Component
{
    public $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function render()
    {
        return view('components.test-print-pdf.footer')->with(['test' => $this->test]);
    }
}