<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTake as Test;

class TestTakeLaravelController extends Controller
{
    public function overview(TestTake $testTake, Request $request)
    {
        $testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->first();

        $current = $request->get('q') ?: '1';

        $data = self::getData($testTake);
        $answers = $this->getAnswers($testTake, $data);

        $playerUrl = route('student.test-take-laravel', ['test_take' => $testTake->uuid]);
        $nav = $data->map(function ($question) use ($answers) {
            $answer = collect($answers)->first(function ($answer, $questionUuid) use ($question) {
                return $question->uuid == $questionUuid;
            });

            return [
                'uuid'     => $question->uuid,
                'id'       => $question->id,
                'answered' => $answer['answered'],
            ];
        });
        $uuid = $testTake->uuid;
        // todo add check or failure when $current out of bounds $data;

        return view('test-take-overview', compact(['data', 'current', 'answers', 'playerUrl', 'nav', 'uuid', 'testParticipant']));
    }


    public function show(TestTake $testTake, Request $request)
    {
        $testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->first();
        $testParticipant->startTestTake();

        $current = $request->get('q') ?: '1';

        $data = self::getData($testTake);
        $answers = $this->getAnswers($testTake, $data);

        $nav = $data->map(function ($question) use ($answers) {
            $answer = collect($answers)->first(function ($answer, $questionUuid) use ($question) {
                return $question->uuid == $questionUuid;
            });

            return [
                'uuid'     => $question->uuid,
                'id'       => $question->id,
                'answered' => $answer['answered'],
            ];
        });

        $uuid = $testTake->uuid;
        // todo add check or failure when $current out of bounds $data;

        return view('test-take', compact(['data', 'current', 'answers', 'nav', 'uuid', 'testParticipant']));
    }

    public function getAnswers($testTake, $testQuestions)
    {
        $result = [];
        TestParticipant::where('test_take_id', $testTake->getKey())
            ->where('user_id', Auth::user()->getKey())
            ->first()
            ->answers
            ->each(function ($answer) use (&$result, $testQuestions) {
                $question = $testQuestions->first(function ($question) use ($answer) {
                    return $question->getKey() === $answer->question_id;
                });
                $result[$question->uuid] = [
                    'id'       => $answer->getKey(),
                    'answer'   => $answer->json,
                    'answered' => $answer->is_answered
                ];
            });
        return $result;
    }


    public static function getData(Test $testTake)
    {
        $visibleAttributes = ['id', 'uuid', 'score', 'type', 'question', 'styling', 'closable'];
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

}
