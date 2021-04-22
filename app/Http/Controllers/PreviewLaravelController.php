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

        return view('test-preview', compact(['data', 'nav', 'uuid', 'answers', 'current']));
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
}
