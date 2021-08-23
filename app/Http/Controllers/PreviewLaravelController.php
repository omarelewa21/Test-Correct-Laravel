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
        $testId = $test->getKey();
        $answers = $data;
        $nav = $this->getNavigationData($data);

        return view('test-preview', compact(['data', 'nav', 'uuid', 'answers', 'current', 'testId']));
    }

    public static function getData(Test $test)
    {
//        $visibleAttributes = ['id', 'uuid', 'score', 'type', 'question', 'styling'];
//
//        return $test->testQuestions->flatMap(function ($testQuestion) use ($visibleAttributes) {
//            if ($testQuestion->question->type === 'GroupQuestion') {
//                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($visibleAttributes) {
//                    $hideAttributes = array_keys($item->question->getAttributes());
//
//                    $item->question->makeHidden($hideAttributes)->makeVisible($visibleAttributes);
//
//                    return $item->question;
//                });
//            }
//            $hideAttributes = array_keys($testQuestion->question->getAttributes());
//            $testQuestion->question->makeHidden($hideAttributes)->makeVisible($visibleAttributes);
//
//            return collect([$testQuestion->question]);
//        });
//        return cache()->remember('data_test_preview' . $test->getKey(), now()->addMinutes(60), function () use ($test) {
            $test->load('testQuestions', 'testQuestions.question', 'testQuestions.question.attachments');
            return $test->testQuestions->flatMap(function ($testQuestion) {
                $testQuestion->question->loadRelated();
                if ($testQuestion->question->type === 'GroupQuestion') {
                    return $testQuestion->question->groupQuestionQuestions->map(function ($item) {
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
            return [
                'id' => $question->id,
                'is_subquestion' => $question->is_subquestion,
                'closeable' => $question->closeable
            ];
        })->toArray();
    }
}
