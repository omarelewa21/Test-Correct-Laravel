<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use tcCore\TestTake;
use tcCore\TestTake as Test;

class TestTakeLaravelController extends Controller
{
    public function show(TestTake $testTake)
    {
        $data = self::getData($testTake);
        $current = $data->first()->uuid;
        return view('test-take', compact(['data', 'current']));
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
