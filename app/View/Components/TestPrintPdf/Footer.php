<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class Footer extends Component
{
    public $title;

    public function __construct($test)
    {
        $this->title = $test->name;
    }

    public function render()
    {
        return view('components.test-print-pdf.footer')->with(['title' => $this->title]);
    }
}