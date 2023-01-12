<?php

namespace tcCore\View\Components\TestPrintPdf;

trait HasTestPrintPdfTypes
{
    public $extraTestPrintPdfCssClass;

    protected function setExtraTestPrintPdfClass() {
        switch ($this->testPrintPdfType) {
            case 'opgaven':
                $this->extraTestPrintPdfCssClass = 'test-print-opgaven-pdf';
                break;
            default:
                $this->extraTestPrintPdfCssClass = '';
                break;
        }
    }
}