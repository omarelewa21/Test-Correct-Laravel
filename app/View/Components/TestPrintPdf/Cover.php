<?php

namespace tcCore\View\Components\TestPrintPdf;

use Illuminate\View\Component;
use tcCore\Test;

class Cover extends Component
{
    public $test;
    public $attachmentsText = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Test $test)
    {
        $this->test = $test;

        if($test->hasPdfAttachments && $this->test->scope == 'exam') {
            $this->attachmentsText = __('test-pdf.cover exam attachments text');
        }
        elseif($test->hasPdfAttachments ) {
            $this->attachmentsText = __('test-pdf.cover test attachments text');
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
                'attachmentsText' => $this->attachmentsText,
            ]);
    }
}
