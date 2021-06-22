<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestionQuestion;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTake as Test;

class TestTakeLaravelController extends Controller
{
    public function overview(TestTake $testTake, Request $request)
    {
        $testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->first();
        if (!$testParticipant->canSeeOverviewPage()) {
            return redirect(config('app.url_login'));
        }

        $current = $request->get('q') ?: '1';

        $data = self::getData($testParticipant);
        $answers = $this->getAnswers($testTake, $data, $testParticipant);

        $playerUrl = route('student.test-take-laravel', ['test_take' => $testTake->uuid]);

        $nav = $this->getNavigationData($data, $answers);
        $uuid = $testTake->uuid;
        // todo add check or failure when $current out of bounds $data;

        return view('test-take-overview', compact(['data', 'current', 'answers', 'playerUrl', 'nav', 'uuid', 'testParticipant']));
    }


    public function show(TestTake $testTake, Request $request)
    {
        $testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->first();
        if (!$testParticipant->startTestTake()) {
            return redirect(config('app.url_login'));
        }

        $data = self::getData($testParticipant);
        $answers = $this->getAnswers($testTake, $data, $testParticipant);
        $nav = $this->getNavigationData($data, $answers);

        $current = (int) $request->get('q') ?: 1;
        if($current < 1){
            $current = 1;
        } else if ($current > $nav->count()) {
            $current = $nav->count();
        }
        $request->merge(['q' => $current]);

        $uuid = $testTake->uuid;
        // todo add check or failure when $current out of bounds $data;

        return view('test-take', compact(['data', 'current', 'answers', 'nav', 'uuid', 'testParticipant']));
    }

    public function getAnswers($testTake, $testQuestions, $testParticipant): array
    {
        $result = [];
        $testParticipant
            ->answers
            ->each(function ($answer) use ($testTake, &$result, $testQuestions) {
                $question = $testQuestions->first(function ($question) use ($answer) {
                    return $question->getKey() === $answer->question_id;
                });

                $groupId = 0;
                $groupCloseable = 0;
                if ($question->is_subquestion) {
                    $groupQuestion = GroupQuestionQuestion::whereQuestionId($question->getKey())->whereIn('group_question_id', function ($query) use ($testTake) {
                        $query->select('question_id')->from('test_questions')->where('test_id', $testTake->test_id);
                    })->first();
                    $groupId = $groupQuestion->group_question_id;
                    $groupCloseable = $groupQuestion->groupQuestion->question->closeable;
                }

                $result[$question->uuid] = [
                    'id'              => $answer->getKey(),
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

    public static function getData($testParticipant)
    {
        return cache()->remember('data'.$testParticipant->getKey(), now()->addMinutes(60), function() use ($testParticipant) {
            $visibleAttributes = ['id', 'uuid', 'score', 'type', 'question', 'styling'];

            return $testParticipant->answers->load('question')->flatMap(function ($answer) use ($visibleAttributes) {
                $hideAttributes = array_keys($answer->question->getAttributes());
                $answer->question->makeHidden($hideAttributes)->makeVisible($visibleAttributes);

                return collect([$answer->question]);
            });
        });
    }

    /**
     * @param $data
     * @param $answers
     * @return mixed
     */
    private function getNavigationData($data, $answers)
    {
        return $data->map(function ($question) use ($answers) {
            $answer = collect($answers)->first(function ($answer, $questionUuid) use ($question) {
                return $question->uuid == $questionUuid;
            });

            return [
                'uuid'      => $question->uuid,
                'id'        => $question->id,
                'answer_id' => $answer['id'],
                'answered'  => $answer['answered'],
                'closeable' => $question->closeable,
                'closed'    => $answer['closed'],
                'group'     => [
                    'id'        => $answer['group_id'],
                    'closeable' => $answer['group_closeable'],
                    'closed'    => $answer['closed_group'],
                ],
            ];
        });
    }
}
