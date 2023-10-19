<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Question;
use tcCore\Test;
use tcCore\User;

class PreviewLaravelController extends Controller
{
    use TestTakeNavigationForController;

    public function show(Test $test, Request $request)
    {

        Gate::authorize('canViewTestDetails',[$test]);

        $data = self::getData($test);
        $current = $request->get('q') ?: '1';
        $uuid = $test->uuid;
        $testId = $test->getKey();
        $nav = $this->getNavigationData($data);
        $styling = $this->getCustomStylingFromQuestions($data);

        return view('test-preview', compact(['data', 'nav', 'uuid', 'current', 'testId', 'styling']));
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

    public function isNotPreview()
    {
        return (Route::currentRouteName()=='teacher.test-preview')?false:true;
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
