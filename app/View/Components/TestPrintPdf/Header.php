<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class Header extends Component
{
    public $test;
    public $testType;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($test)
    {
        $this->test = $test;

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
        return view('components.test-print-pdf.header')->with([
            'test'     => $this->test,
            'testType' => $this->testType,
        ]);
    }
}
