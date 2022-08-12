<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Controllers\PrintTestController;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Test;

class PdfDownloadModal extends ModalComponent
{
    public string $waitingScreenHtml;
    public string $translation;
    public string $uuid;
    public $test;
    public bool $testHasPdfAttachments;

    public bool $displayValueRequiredMessage = false;
    protected static array $maxWidths = [
        'w-modal' => 'max-w-[720px]',
    ];

    public static function modalMaxWidth(): string
    {
        return 'w-modal';
    }

    public function mount($test)
    {
        $this->uuid = $test;
        $this->test = Test::findByUuid($test);

        $this->testHasPdfAttachments = $this->test->hasPdfAttachments;

        $this->setDownloadWaitingScreenHtml();
    }

    public function getTemporaryLoginToPdfForTest()
    {
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => sprintf('/tests/view/%s', $this->uuid),
                'page_action' => sprintf("Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);", $this->uuid),
            ],
        ]);

        return $controller->toCakeUrl($request);
    }

    private function setDownloadWaitingScreenHtml()
    {
        $this->translation = __('test-pdf.pdf_download_wait_text');

        $this->waitingScreenHtml = "<html><body><div style=\"background-color: #ff0000;\">test</div></body> </html>";
    }

    public function render()
    {
        return view('livewire.teacher.pdf-download-modal');
    }

    public function close()
    {
        $this->closeModal();
    }
}
