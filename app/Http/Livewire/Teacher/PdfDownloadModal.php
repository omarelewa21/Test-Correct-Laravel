<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Test;
use tcCore\TestTake;

class PdfDownloadModal extends TCModalComponent
{
    public string $waitingScreenHtml;
    public string $translation;
    public $test;
    public $testTake;
    public bool $testHasPdfAttachments;
    public bool $testTakeHasAnswers = false;

    public bool $displayValueRequiredMessage = false;
    protected static array $maxWidths = [
        'w-modal' => 'max-w-[720px]',
    ];

    public static function modalMaxWidth(): string
    {
        return 'w-modal';
    }

    public function mount($uuid, bool $testTake = false)
    {
        if ($testTake) {
            $this->testTake = TestTake::whereUuid($uuid)->first();
            $this->test = $this->testTake->test;
            $this->testTakeHasAnswers = ((bool)$this->testTake->testParticipants()->count());
            //$this->testTake->test_take_status_id >= 8; //only show if testTake discussed or Rated? or just if it has answers?
        } else {
            $this->test = Test::findByUuid($uuid);
        }

        if(!Gate::allows('canViewTestDetails',[$this->test])){
            $this->forceClose()->closeModal();
            return;
        }

        $this->testHasPdfAttachments = $this->test->hasPdfAttachments;

        $this->setDownloadWaitingScreenHtml();
    }

    public function getTemporaryLoginToPdfForTest()
    {
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => sprintf('/tests/view/%s', $this->test->uuid),
                'page_action' => sprintf("Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);", $this->test->uuid),
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
