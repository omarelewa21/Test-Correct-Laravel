<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\MultipleChoiceQuestion;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\Question;
use tcCore\TestParticipant;
use tcCore\TestTake as Test;


class TestTake extends Component
{

    public $testQuestions;
    public $question;
    protected $queryString = ['question'];
    public $content;
    public $mainQuestion;
    public $component;

    public function mount(Test $test_take)
    {


        $this->testQuestions = self::getData($test_take);
        session()->put('data', serialize($this->testQuestions));
        $this->question = $this->testQuestions->get(3)->uuid;

        $this->setMainQuestion($this->question);
    }

    public function hydrate()
    {
        dump('hydrate');

        $q = unserialize(session()->get('data'))->get(3);
        dump($q->multipleChoiceQuestionAnswers);

        $this->testQuestions = unserialize(session()->get('data'));
    }

    public function previousQuestion()
    {
        $this->question--;
        $this->setMainQuestion($this->question);
    }

    public function nextQuestion()
    {
        $this->question++;
        $this->setMainQuestion($this->question);

    }


    public function render()
    {
        dump('render test-take');
        return view('livewire.student.test-take')->layout('layouts.app');
    }

    public function setMainQuestion($questionUuid)
    {
        $this->question = $questionUuid;
//        $this->mainQuestion = $this->testQuestions->first(function ($item, $index) use ($questionUuid) {
//            return $item->uuid === $questionUuid;
//        });
        $this->mainQuestion = Question::whereUuid($questionUuid)->first();
        $this->mainQuestion = MultipleChoiceQuestion::whereUuid($questionUuid)->first();

        $this->component = 'question.'.Str::kebab($this->mainQuestion->type);
    }

    public static function getData(Test $testTake)
    {
        $visibleAttributes = ['id', 'uuid', 'score', 'type', 'question', 'styling'];
        $testTake->load(['test', 'test.testQuestions', 'test.testQuestions.question'])->get();

        return $testTake->test->testQuestions->flatMap(function ($testQuestion) use ($visibleAttributes) {
            if ($testQuestion->question->type === 'GroupQuestion') {
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($visibleAttributes) {
                    $item->question->makeVisible($visibleAttributes);
//                        $item->question->loadRelated();

                    return $item->question;
                });
            }
            $testQuestion->question->makeVisible($visibleAttributes);
//            $testQuestion->question->loadRelated();
            return collect([$testQuestion->question]);
        });
    }

//    public static function loadRelations($question)
//    {
//        switch (get_class($question)) {
//            case 'tcCore\OpenQuestion':
//                break;
//            case 'tcCore\MultipleChoiceQuestion':
//                $question->load('multipleChoiceQuestionAnswers');
//                break;
//            case 'tcCore\CompletionQuestion' :
//                break;
//            case 'tcCore\MatchingQuestion' :
//                break;
//            default:
//
//
//        }
//
//    }
}
