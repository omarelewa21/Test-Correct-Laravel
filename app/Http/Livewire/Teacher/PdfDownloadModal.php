<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Controllers\PrintTestController;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Test;

class PdfDownloadModal extends ModalComponent
{
    public $selectedOption = null;

    public $displayValueRequiredMessage = false;
    protected static array $maxWidths = [
        'w-modal'  => 'max-w-[720px]',
    ];

    public static function modalMaxWidth(): string
    {
        return 'w-modal';
    }

    public function mount($test)
    {
        $this->uuid = $test;
        $this->test = Test::findByUuid($test);
    }

    public function submit($selectedOption)
    {
        $this->selectedOption = $selectedOption;

        //todo
        // handle choice of pdf download
        switch($selectedOption) {
            case 'testpdf':
                //todo add some sort of waiting page / ajax or wait
                return $this->redirectRoute('teacher.preview.test_pdf', ['test' => $this->uuid]);
                break;
            case 'attachments':
                //todo add redirect to cake
//                let response = await $wire.getTemporaryLoginToPdfForTest();
//                window.open(response, '_blank');

                break;
            case 'answermodel':
                //todo add some sort of waiting page / ajax or wait
                return $this->redirectRoute('teacher.test-answer-model', ['test' => $this->uuid]);
                break;
            case 'studentanswers':
                // doesnt exist yet.
                break;
        }
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

    public function render()
    {
        return view('livewire.teacher.pdf-download-modal');
    }

    public function close()
    {
        $this->closeModal();
    }
}
