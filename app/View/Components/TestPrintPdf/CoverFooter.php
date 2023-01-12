<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;

class CoverFooter extends Component
{
    use HasTestPrintPdfTypes;

    public $data;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public $test,
        public $testTake = null,
        public $testPrintPdfType = 'toets',
    )
    {
        $amountOfQuestions = $test->getAmountOfQuestions()['regular'];

        $this->data = [
            'amountOfQuestions' => $amountOfQuestions,
            'maxScore'          => $this->test->maxScore(),
            'weight'            => $this->testTake->weight ?? 0,
            'teacher'           => $this->testTake->user->name ?? $this->test->author->name,
        ];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        $this->setExtraTestPrintPdfClass();

        return view('components.test-print-pdf.cover-footer')
            ->with([
                'test' => $this->test,
                'data' => $this->data,
                'extraCssClass' => $this->extraTestPrintPdfCssClass,
            ]);
    }
}
