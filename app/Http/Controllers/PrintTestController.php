<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use iio\libmergepdf\Driver\TcpdiDriver;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\TestAttachmentsHelper;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;
use Facades\tcCore\Http\Controllers\PdfController;
use tcCore\View\Components\TestPrintPdf\Cover;
use tcCore\View\Components\TestPrintPdf\CoverFooter;
use tcCore\View\Components\TestPrintPdf\CoverHeader;
use tcCore\View\Components\TestPrintPdf\Footer;
use tcCore\View\Components\TestPrintPdf\Header;

class PrintTestController extends Controller
{
    use TestTakeNavigationForController;

    private $test = null;
    private $testTake = null;

    private $testOpgavenPdf = false;

    public function showTest(Test $test, Request $request)
    {
        $this->test = $test;

        return $this->createPdfDownload();
    }

    public function downloadTestAttachments(Test $test, Request $request)
    {
        return TestAttachmentsHelper::createZipDownload($test);
    }

    public function showTestOpgaven(Test $test, Request $request)
    {
        $this->testOpgavenPdf = true;

        return $this->showTest($test, $request);
    }

    public function showTestTake(TestTake $testTake, Request $request)
    {
        $this->testTake = $testTake;
        $this->test = $testTake->test;

        return $this->createPdfDownload();
    }

    public function showTestPdfAttachments(Test $test)
    {
        //this method breaks on pdf files with encryption or new pdf file versions

        $this->test = $test;

        return $this->createPdfAttachmentsDownload();
    }

    public function showGradeList(TestTake $testTake)
    {
        $this->testTake = $testTake->load([
            'test:id,name',
            'testParticipants:id,user_id,test_take_id,rating',
            'testParticipants.user' => fn($query) => $query->select(
                'id',
                'name',
                'name_first',
                'name_suffix'
            )->withTrashed(),
        ]);
        $this->testTake->testParticipants
            ->each(fn($participant) => $participant->user->fullName = $participant->user->name_full);

        $titleForPdf = sprintf('%s %s', __('test-take.Cijferlijst voor'), $this->testTake->test->name);

        $html = view(
            'grade-list',
            [
                'testParticipants' => $testTake->testParticipants,
                'titleForPdfPage'  => $titleForPdf,
            ]
        )
            ->render();
        $pdf = PdfController::HtmlToPdfFromString($html);


        return $this->makeResponseForPdf($pdf, $titleForPdf);
    }

    private function createPdfDownload()
    {
        $coverPdf = $this->generateCoverPdf();
        $mainPdf = $this->generateMainPdf();

        $mergedPdf = $this->mergePdfFiles($coverPdf, $mainPdf);

        $titleForPdfPage = __('test-pdf.printversion_test') . ' ' . $this->test->name . ' ' . Carbon::now()->format(
                'd-m-Y H:i'
            );

        return $this->makeResponseForPdf($mergedPdf, $titleForPdfPage);
    }

    private function createPdfAttachmentsDownload()
    {
        $pdf = $this->generatePdfAttachmentsPdf();

        $titleForPdf = __('test-pdf.printversion_test_attachments') . ' ' . $this->test->name . ' ' . Carbon::now(
            )->format('d-m-Y H:i');

        return $this->makeResponseForPdf($pdf, $titleForPdf);
    }

    private function mergePdfFiles($coverPdf, $mainPdf)
    {
        $disk = Storage::disk('temp_pdf');

        $merger = new Merger(new TcpdiDriver());
        $merger->addFile($disk->path($coverPdf));
        $merger->addFile($disk->path($mainPdf));

        $createdPdf = $merger->merge();

        $disk->delete($coverPdf);
        $disk->delete($mainPdf);

        return $createdPdf;
    }

    private function generatePdfAttachmentsPdf()
    {
        if (!$this->test->hasPdfAttachments) {
            return false;
        }

        $pdfAttachments = $this->test->pdfAttachments;

        if ($pdfAttachments->isNotEmpty()) {
            $merger = new Merger(new TcpdiDriver());
            foreach ($pdfAttachments as $attachment) {
                $merger->addFile($attachment->getCurrentPath());
            }
            return $merger->merge();
        }
        return false;
    }

    private function generateCoverPdf()
    {
        $showCoverExplanationText = !$this->testOpgavenPdf;

        $cover = (new Cover(
            $this->test, $showCoverExplanationText, $this->testOpgavenPdf ? 'opgaven' : 'toets'
        ))->render();
        $header = (new CoverHeader($this->test, $this->testTake, $this->testOpgavenPdf ? 'opgaven' : 'toets'))->render(
        );
        $footer = (new CoverFooter($this->test, $this->testTake, $this->testOpgavenPdf ? 'opgaven' : 'toets'))->render(
        );

        return PdfController::createTestPrintPdf($cover, $header, $footer);
    }

    private function generateMainPdf()
    {
        $test = $this->test;
        $data = self::getData($this->test);
        $attachment_counters = $this->createAttachmentCountersFromData($data);
        $answers = [];

        $footer = (new Footer($this->test, $this->testOpgavenPdf ? 'opgaven' : 'toets'))->render();
        $header = (new Header($this->test, $this->testOpgavenPdf ? 'opgaven' : 'toets'))->render();

        $nav = $this->getNavigationData($data, $answers);
        $uuid = '';
        // todo add check or failure when $current out of bounds $data;
        $styling = $this->getCustomStylingFromQuestions($data);

        $titleForPdfPage = __('test-pdf.printversion_test') . ' ' . $this->test->name . ' ' . Carbon::now()->format(
                'd-m-Y H:i'
            );
        view()->share('titleForPdfPage', $titleForPdfPage);
        ini_set('max_execution_time', '90');

        if (!$this->testOpgavenPdf) {
            $html = view('test-print', compact(['data', 'nav', 'styling', 'test', 'attachment_counters']))->render();
        } else {
            $html = view(
                'test-opgaven-print',
                compact(['data', 'nav', 'styling', 'test', 'attachment_counters'])
            )->render();
        }

        return PdfController::createTestPrintPdf($html, $header, $footer);
    }

    public static function getData(Test $test)
    {
        $test->load('testQuestions', 'testQuestions.question', 'testQuestions.question.attachments');
        return $test->testQuestions
            ->sortBy('order')
            //->when($test->shuffle, fn($testQuestions) => $testQuestions->shuffle()) //23-01-09: removed shuffling of testQuestions by request of business
            ->flatMap(function ($testQuestion) {
                $testQuestion->question->loadRelated();
                if ($testQuestion->question->type === 'GroupQuestion') {
                    $groupQuestion = $testQuestion->question;

                    if ($testQuestion->question->groupquestion_type === 'carousel') {
                        //filters questions to needed amount for carousel
                        return collect(
                            $testQuestion->question->filterQuestionsForCarousel(
                                $testQuestion->question->groupQuestionQuestions->map(
                                    function ($item) use ($groupQuestion) {
                                        $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                                        return $item->question;
                                    }
                                )->toArray()
                            )
                        )->map(fn($item) => Question::find($item['id']))
                            ->prepend($testQuestion->question)
                            ->add($testQuestion->question);
                    }

                    return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                        $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                        return $item->question;
                    })->prepend($testQuestion->question)->add($testQuestion->question);
                }
                return collect([$testQuestion->question]);
            });
    }

    private function createAttachmentCountersFromData($data)
    {
        $result = [
            'image' => [],
            'pdf'   => [],
            'audio' => [],
            'video' => [],
        ];
        $data->each(function ($question) use (&$result) {
            $question->attachments->map(function ($attachment) use (&$result) {
                $fileType = $attachment->getFileType();
                if (!isset($result[$fileType][$attachment->getKey()])) {
                    $result[$fileType][$attachment->getKey()] = count($result[$fileType]) + 1;
                }
                return $attachment;
            });
        });
        return $result;
    }

    private function getNavigationData($data)
    {
        return collect($data)->map(function ($question) {
            $question->question = null;
            $closeableAudio = $this->getCloseableAudio($question);
            return [
                'id'              => $question->id,
                'is_subquestion'  => $question->is_subquestion,
                'closeable'       => $question->closeable,
                'closeable_audio' => $closeableAudio
            ];
        })->toArray();
    }

    private function getCustomStylingFromQuestions($data)
    {
        return $data->map(function ($question) {
            return $question->getQuestionInstance()->styling;
        })->unique()->implode(' ');
    }

    /**
     * @param string $mergedPdf
     * @param string $titleForPdfPage
     * @return \Illuminate\Http\Response|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function makeResponseForPdf(string $mergedPdf, string $titleForPdfPage): mixed
    {
        return response()->make($mergedPdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $titleForPdfPage . '.pdf"'
        ]);
    }

}
