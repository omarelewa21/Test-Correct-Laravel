<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class Footer extends Component
{
    use HasTestPrintPdfTypes;

    public function __construct(
        public $test,
        public $testPrintPdfType = 'toets',
    )
    {
    }

    public function render()
    {
        $this->setExtraTestPrintPdfClass();

        return view('components.test-print-pdf.footer')
            ->with([
                'test' => $this->test,
                'extraCssClass' => $this->extraTestPrintPdfCssClass,
            ]);
    }
}