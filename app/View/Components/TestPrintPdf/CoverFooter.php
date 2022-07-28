<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class CoverFooter extends Component
{
    public $test;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($test)
    {
        $this->test = $test;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.test-print-pdf.cover-footer')->with(['test' => $this->test]);;
    }
}
