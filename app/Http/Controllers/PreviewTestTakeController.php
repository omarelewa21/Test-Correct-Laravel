<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;
use Facades\tcCore\Http\Controllers\PdfController;

class PreviewTestTakeController extends Controller
{
    use TestTakeNavigationForController;

    public function show(TestTake $testTake, Request $request)
    {
        $test = $testTake->test;
        $current = '1';
        $data = self::getData($test);
        $answers = [];

        $playerUrl = '';
        $testParticipants = $testTake->testParticipants;
        $html = view('test-take-overview-preview', compact(['testParticipants','testTake']))->render();


        return response()->make(PdfController::HtmlToPdfFromString($html), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="toets.pdf"'
        ]);



        foreach ($testParticipants as $testParticipant){

            $data = self::getData($testParticipant, $testTake);
            $answers = $this->getAnswers($testTake, $data, $testParticipant);

//            $data = $this->applyAnswerOrderForParticipant($data, $answers);
            $playerUrl = route('student.test-take-laravel', ['test_take' => $testTake->uuid]);

            $nav = $this->getNavigationData($data, $answers);
            $uuid = $testTake->uuid;
            $styling = $this->getCustomStylingFromQuestions($data);
            $html .= view('test-take-overview', compact(['data', 'current', 'answers', 'playerUrl', 'nav', 'uuid', 'testParticipant', 'styling']))->render();
        }

        return response()->make(PdfController::HtmlToPdfFromString($html), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="toets.pdf"'
        ]);
    }

    public static function getData($testParticipant, $testTake)
    {
        return cache()->remember('data_test_take_' . $testTake->getKey(), now()->addMinutes(60), function () use ($testTake) {
            $testTake->load('test', 'test.testQuestions', 'test.testQuestions.question', 'test.testQuestions.question.attachments');
            return $testTake->test->testQuestions->flatMap(function ($testQuestion) {
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
        });
    }

    public function getAnswers($testTake, $testQuestions, $testParticipant): array
    {
        $result = [];
        $testParticipant
            ->answers
            ->sortBy(function ($answer) {
                return $answer->order;
            })
            ->each(function ($answer) use ($testTake, &$result, $testQuestions) {
                $question = $testQuestions->first(function ($question) use ($answer) {
                    return $question->getKey() === $answer->question_id;
                });
                $groupId = 0;
                $groupCloseable = 0;
                if ($question->is_subquestion) {
                    $groupQuestionQuestion = GroupQuestionQuestion::select('group_question_questions.group_question_id', 'questions.closeable')
                        ->where('group_question_questions.question_id', $question->getKey())
                        ->whereIn('group_question_questions.group_question_id', function ($query) use ($testTake) {
                            $query->select('question_id')->from('test_questions')->where('test_id', $testTake->test_id);
                        })
                        ->leftJoin('group_questions', 'group_questions.id', '=', 'group_question_questions.group_question_id')
                        ->leftJoin('questions', 'questions.id', '=', 'group_questions.id')
                        ->get();
                    $groupId = $groupQuestionQuestion->first()->group_question_id;
                    $groupCloseable = $groupQuestionQuestion->first()->closeable;
                }

                $result[$question->uuid] = [
                    'id'              => $answer->getKey(),
                    'uuid'            => $answer->uuid,
                    'order'           => $answer->order,
                    'question_id'     => $answer->question_id,
                    'answer'          => $answer->json,
                    'answered'        => $answer->is_answered,
                    'closed'          => $answer->closed,
                    'closed_group'    => $answer->closed_group,
                    'group_id'        => $groupId,
                    'group_closeable' => $groupCloseable
                ];
            });
        return $result;
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
