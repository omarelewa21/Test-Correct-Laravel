<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\User;
use Facades\tcCore\Http\Controllers\PdfController;

class PreviewAnswerModelController extends Controller
{
    use TestTakeNavigationForController;

    public function show(Test $test, Request $request)
    {

        $current = '1';
        $data = self::getData($test);
        $answers = [];

        $playerUrl = '';

        $testParticipant = new TestParticipant();

        $nav = $this->getNavigationData($data, $answers);
        $uuid = '';
        // todo add check or failure when $current out of bounds $data;
        $styling = $this->getCustomStylingFromQuestions($data);
//        $styling = $styling.$this->getAppCssForPdf();
//        return view('test-answer-model-overview',compact(['data', 'current', 'answers', 'playerUrl', 'nav', 'uuid', 'testParticipant', 'styling', 'test']));
        $titleForPdfPage = __('Antwoord model').' '.$test->name.' '.Carbon::now()->format('d-m-Y H:i');
        view()->share('titleForPdfPage',$titleForPdfPage);
        ini_set('max_execution_time', '90');
        $html = view('test-answer-model-overview',compact(['data', 'current', 'answers', 'playerUrl', 'nav', 'uuid', 'testParticipant', 'styling', 'test']))->render();
        return response()->make(PdfController::HtmlToPdfFromString($html), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="toets.pdf"'
        ]);
    }

    public static function getData(Test $test)
    {
            $test->load('testQuestions', 'testQuestions.question', 'testQuestions.question.attachments');
            return $test->testQuestions->sortBy('order')->flatMap(function ($testQuestion) {
                $testQuestion->question->loadRelated();
                if ($testQuestion->question->type === 'GroupQuestion') {
                    $groupQuestion = $testQuestion->question;
                    return $testQuestion->question->groupQuestionQuestions->map(function ($item) use($groupQuestion){
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
                'id' => $question->id,
                'is_subquestion' => $question->is_subquestion,
                'closeable' => $question->closeable,
                'closeable_audio' => $closeableAudio
            ];
        })->toArray();
    }

    private function getCustomStylingFromQuestions($data)
    {
        return $data->map(function($question) {
            return $question->getQuestionInstance()->styling;
        })->unique()->implode(' ');
    }

}
