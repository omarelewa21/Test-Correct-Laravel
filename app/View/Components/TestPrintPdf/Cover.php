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

        $pdfAttachmentsCount = $this->test->pdfAttachments->count();

        if ($pdfAttachmentsCount < 1) {
            return;
        }
        if ($pdfAttachmentsCount === 1) {
            $this->attachmentsText = $this->test->scope == 'exam' ?
                __('test-pdf.cover exam attachments singular') :
                __('test-pdf.cover test attachments singular');
            return;
        }

        $this->attachmentsText = $this->test->scope == 'exam' ?
            __('test-pdf.cover exam attachments plural') :
            __('test-pdf.cover test attachments plural');

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
                'test'            => $this->test,
                'attachmentsText' => $this->attachmentsText,
            ]);
    }
}
