<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTake as Test;

class TestTakeLaravelController extends Controller
{
    public function show(TestTake $testTake, Request $request)
    {
        $current = $request->get('question')?: '1';

        $data = self::getData($testTake);
        $answers = $this->getAnswers($testTake, $data);

// todo add check or failure when $current out of bounds $data;

        return view('test-take', compact(['data', 'current', 'answers']));
    }

    public function getAnswers($testTake, $testQuestions) {
        $result = [];
         TestParticipant::where('test_take_id', $testTake->getKey())
            ->where('user_id', Auth::user()->getKey())
            ->first()
            ->answers
            ->each(function ($answer) use (&$result, $testQuestions) {
                $question = $testQuestions->first(function ($question) use ($answer) {
                    return $question->getKey() === $answer->question_id;
                });
                $result[$question->uuid] = ['id' => $answer->getKey(), 'answer' => $answer->json];
            });
         return $result;
    }


    public static function getData(Test $testTake)
    {
        $visibleAttributes = ['id', 'uuid', 'score', 'type', 'question', 'styling'];
        $testTake->load(['test', 'test.testQuestions', 'test.testQuestions.question'])->get();

        return $testTake->test->testQuestions->flatMap(function ($testQuestion) use ($visibleAttributes) {
            if ($testQuestion->question->type === 'GroupQuestion') {
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($visibleAttributes) {
                    $hideAttributes = array_keys($item->question->getAttributes());

                    $item->question->makeHidden($hideAttributes)->makeVisible($visibleAttributes);

                    return $item->question;
                });
            }
            $hideAttributes = array_keys($testQuestion->question->getAttributes());
            $testQuestion->question->makeHidden($hideAttributes)->makeVisible($visibleAttributes);

            return collect([$testQuestion->question]);
        });
    }
    //
}
