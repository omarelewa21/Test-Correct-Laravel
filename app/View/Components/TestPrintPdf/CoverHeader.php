<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class CoverHeader extends Component
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
        return view('components.test-print-pdf.cover-header')->with(['test' => $this->test]);
    }
}
