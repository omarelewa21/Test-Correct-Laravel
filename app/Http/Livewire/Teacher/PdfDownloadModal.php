<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
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

    public function mount($uuid, $testTake = null): void
    {
        if ($testTake) {
            $this->testTake = TestTake::whereUuid($testTake)
                ->with(['test'])
                ->withCount('testParticipants')
                ->first();
            $this->test = $this->testTake->test;
            $this->testTakeHasAnswers = (bool)$this->testTake->test_participants_count;
        } else {
            $this->test = Test::findByUuid($uuid);
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
                'page_action' => sprintf(
                    "Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);",
                    $this->test->uuid
                ),
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

    public function downloadLinks(): array
    {
        return collect($this->downloadOptions())
            ->mapWithKeys(fn($data, $key) => [$key => $data['link']])
            ->toArray();
    }

    public function downloadOptions(): array
    {
        $options = [
            'testopgavenpdf'  => [
                'link'    => route('teacher.preview.test_pdf', ['test' => $this->test->uuid]),
                'sticker' => 'test-export-questions',
                'active'  => true,
                'show'    => true,
                'title'   => __('cms.toets_opgaven_pdf'),
                'text'    => __('cms.toets_opgaven_pdf_omschrijving'),
            ],
            'testtakepdf'     => [
                'link'    => '',
                'sticker' => 'test-export-questions',
                'active'  => true,
                'show'    => !empty($this->testTake),
                'title'   => __('cms.toets_pdf'),
                'text'    => __('cms.toets_pdf_omschrijving'),
            ],
            'testpdf'         => [
                'link'    => route('teacher.preview.test_opgaven_pdf', ['test' => $this->test->uuid]),
                'sticker' => 'test-export-questions',
                'active'  => true,
                'show'    => empty($this->testTake),
                'title'   => __('cms.toets_pdf'),
                'text'    => __('cms.toets_pdf_omschrijving'),
            ],
            'testattachments' => [
                'link'    => route('teacher.preview.test_attachments', ['test' => $this->test->uuid]),
                'sticker' => 'test-export-attachments',
                'active'  => $this->test->attachments->isNotEmpty(),
                'show'    => true,
                'title'   => __('cms.bijlagen'),
                'text'    => __('cms.alle bijlagen'),
            ],
            'answermodel'     => [
                'link'    => route('teacher.test-answer-model', ['test' => $this->test->uuid]),
                'sticker' => 'test-export-answermodel',
                'active'  => true,
                'show'    => true,
                'title'   => __('cms.antwoordmodellen'),
                'text'    => __('cms.antwoordmodellen_omschrijving'),
            ],
            'studentanswers'  => [
                'link'    => '',
                'sticker' => 'test-export-answers',
                'active'  => $this->testTakeHasAnswers,
                'show'    => true,
                'title'   => __('cms.antwoorden'),
                'text'    => __('cms.antwoorden_omschrijving'),
            ],
        ];

        if ($this->testTake) {
            $options['testtakepdf']['link'] = route(
                'teacher.preview.test_take_pdf',
                ['test_take' => $this->testTake->uuid]
            );
            $options['studentanswers']['link'] = route(
                'teacher.preview.test_take',
                ['test_take' => $this->testTake->uuid]
            );
        }

        return $options;
    }
}