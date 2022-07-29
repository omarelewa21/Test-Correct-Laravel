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

        $amountOfQuestions = collect($test->getAmountOfQuestions())->reduce(function ($carry, $item) {
            return $carry + $item;
        }, 0);

        $this->data = [
            'amountOfQuestions' => $amountOfQuestions,
            'maxScore' => $this->test->maxScore(),
            'weight' => 0, //todo implement for TestTake
        ];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.test-print-pdf.cover-footer')
            ->with([
                'test' => $this->test,
                'data' => $this->data,
            ]);
    }
}
