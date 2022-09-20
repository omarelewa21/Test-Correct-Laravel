<?php

namespace tcCore\View\Components\TestPrintPdf;

use Carbon\Carbon;
use Illuminate\View\Component;
use tcCore\Lib\Repositories\PeriodRepository;

class CoverHeader extends Component
{
    public $test;
    public $testTake;
    public $testType = 'test';
    public $date = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($test, $testTake = null)
    {
        $this->test = $test;
        $this->testTake = $testTake;

        if ($this->test->scope == 'exam') {
            $this->testType = 'exam';
        }
        if ($this->test->scope == 'cito') {
            $this->testType = 'cito';
        }

        //todo change date to date of testTake
        if($this->testTake){
            Carbon::setlocale(config('app.locale'));
            $this->date = $this->testTake->time_start->translatedFormat('l j F');
        }

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
                'period'   => PeriodRepository::getCurrentPeriod()->name ?? '',
            ]);
    }
}
