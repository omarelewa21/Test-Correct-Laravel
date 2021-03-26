<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestionQuestion;
use tcCore\Test;
use tcCore\User;

class PreviewLaravelController extends Controller
{
    public function show(Test $test, Request $request)
    {
        $data = self::getData($test);
        $current = $request->get('q') ?: '1';
        $uuid = $test->uuid;
        $answers = $data;
        $nav = $data;
//        $answers = self::getAnswers($data);
//        $nav = $this->getNavigationData($data);

        return view('test-preview', compact(['data', 'nav', 'uuid', 'answers', 'current']));
    }

    public function getAnswers($data): array
    {
        $result = [];

        $testParticipant->answers->each(function ($answer) use ($testTake, &$result, $testQuestions) {
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

    public static function getData(Test $test)
    {
        $visibleAttributes = ['id', 'uuid', 'score', 'type', 'question', 'styling'];

        return $test->testQuestions->flatMap(function ($testQuestion) use ($visibleAttributes) {
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

    /**
     * @param $data
     * @param $answers
     * @return mixed
     */
    private function getNavigationData($data)
    {
        return $data->map(function ($question) {
            $answer = $question->question->first(function ($answer, $questionUuid) use ($question) {

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
