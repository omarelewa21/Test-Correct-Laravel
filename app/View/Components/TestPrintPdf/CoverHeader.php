<?php

namespace tcCore\View\Components\TestPrintPdf;

use Carbon\Carbon;
use Illuminate\View\Component;

class CoverHeader extends Component
{
    public $test;
    public $testType = 'toets';

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

        //todo change date to date of testTake
        Carbon::setlocale(config('app.locale'));
        $this->date = Carbon::now()->translatedFormat('l d F');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.test-print-pdf.cover-header')
            ->with([
                'test'     => $this->test,
                'testType' => $this->testType,
                'date'     => $this->date,
            ]);
    }
}
