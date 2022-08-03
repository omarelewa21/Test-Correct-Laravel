<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;
use tcCore\Test;

class Cover extends Component
{
    public $test;
    public $showExamAttachmentsText = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Test $test)
    {
        $this->test = $test;

        //todo logic when to show attachments informational text
        //$this->showAttachmentsText = false;
        if($this->test->scope == 'exam'){
            $this->showExamAttachmentsText = true;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.test-print-pdf.cover')
            ->with([
                'test'                => $this->test,
                'showExamAttachmentsText' => $this->showExamAttachmentsText,
            ]);
    }
}
