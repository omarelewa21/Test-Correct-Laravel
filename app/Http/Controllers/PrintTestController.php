<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use tcCore\GroupQuestionQuestion;
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

    public function showTest(Test $test, Request $request)
    {
        $this->test = $test;

        $coverPdf = $this->generateCoverPdf($test);
        $mainPdf = $this->generateMainPdf();

        $mergedPdf = $this->mergePdfFiles($coverPdf, $mainPdf);


        return response()->make($mergedPdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="toets.pdf"'
        ]);
    }

    public function showTestTake(TestTake $testTake, Request $request)
    {
        $this->testTake = $testTake;
        $this->test = $testTake->test;

        $coverPdf = $this->generateCoverPdf($this->test);
        $mainPdf = $this->generateMainPdf();

        $mergedPdf = $this->mergePdfFiles($coverPdf, $mainPdf);


        return response()->make($mergedPdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="toets.pdf"'
        ]);
    }


    private function mergePdfFiles($coverPdf, $mainPdf)
    {
        $disk = Storage::disk('temp_pdf');

        $merger = new Merger;
        $merger->addFile($disk->path($coverPdf));
        $merger->addFile($disk->path($mainPdf));

        $createdPdf = $merger->merge();

        $disk->delete($coverPdf);
        $disk->delete($mainPdf);

        return $createdPdf;
    }

    private function generateCoverPdf()
    {
        $titleForPdfPage = __('Printversie toets:') . ' ' . $this->test->name . ' ' . Carbon::now()->format('d-m-Y H:i');
        view()->share('titleForPdfPage', $titleForPdfPage);

        $cover = (new Cover($this->test))->render();
        $header = (new CoverHeader($this->test, $this->testTake))->render();
        $footer = (new CoverFooter($this->test, $this->testTake))->render();
        Storage::put('temp/coverfooter.html',$footer);

        return PdfController::createTestPrintPdf($cover, $header, $footer);
    }

    private function generateMainPdf()
    {
        $test = $this->test;
        $data = self::getData($this->test);
        $answers = [];

        $footer = (new Footer($this->test))->render();
        $header = (new Header($this->test))->render();

        $nav = $this->getNavigationData($data, $answers);
        $uuid = '';
        // todo add check or failure when $current out of bounds $data;
        $styling = $this->getCustomStylingFromQuestions($data);

        $titleForPdfPage = __('Printversie toets:') . ' ' . $this->test->name . ' ' . Carbon::now()->format('d-m-Y H:i');
        view()->share('titleForPdfPage', $titleForPdfPage);
        ini_set('max_execution_time', '90');
        $html = view('test-print', compact(['data', 'answers', 'nav', 'styling', 'test']))->render();

        return PdfController::createTestPrintPdf($html, $header, $footer);
    }

    public static function getData(Test $test)
    {
        $test->load('testQuestions', 'testQuestions.question', 'testQuestions.question.attachments');
        return $test->testQuestions->sortBy('order')->flatMap(function ($testQuestion) {
            $testQuestion->question->loadRelated();
            if ($testQuestion->question->type === 'GroupQuestion') {
                $groupQuestion = $testQuestion->question;
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                    $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                    return $item->question;
                });
            }
            return collect([$testQuestion->question]);
        });
//        });
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

}
