<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class Header extends Component
{
    use HasTestPrintPdfTypes;

    public $testType = 'test';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public $test,
        public $testPrintPdfType = 'toets',
    )
    {
        if ($this->test->scope == 'exam') {
            $this->testType = 'exam';
        }
        if ($this->test->scope == 'cito') {
            $this->testType = 'cito';
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        $this->setExtraTestPrintPdfClass();

        return view('components.test-print-pdf.header')->with([
            'test'          => $this->test,
            'testType'      => $this->testType,
            'extraCssClass' => $this->extraTestPrintPdfCssClass,
        ]);
    }
}
