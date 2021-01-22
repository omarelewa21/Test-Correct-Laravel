<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
    public $number = 1;
    public $caption;
    public $answer;

    public $answers = [];

    protected $listeners = ['updateAnswer' => 'updateAnswer'];

    public function mount(Test $test_take)
    {
        $this->testQuestions = self::getData($test_take);
        session()->put('data', serialize($this->testQuestions));
        $this->setMainQuestion($this->question ?: $this->testQuestions->first()->uuid);



        TestParticipant::where('test_take_id', $test_take->getKey())
            ->where('user_id', Auth::user()->getKey())
            ->first()
            ->answers
            ->each(function ($answer) {
                $question = $this->testQuestions->first(function ($question) use ($answer) {
                    return $question->getKey() === $answer->question_id;
                });
                $this->answers[$question->uuid] = ['id' => $answer->getKey(), 'answer' => $answer->json];
            });
    }

    public function getState($uuid)
    {
        if($uuid === $this->question) {
            return 'active';
        }

        if (array_key_exists($uuid, $this->answers)) {
            if (!empty($this->answers[$uuid]['answer'])) {
                return 'complete';
            }
        }

        return '';
    }


    private function getCurrentAnswer($questionUuid)
    {
        if (array_key_exists($questionUuid, $this->answers)) {
            return json_decode($this->answers[$questionUuid]['answer']);
        }

        return '';
    }

    public function hydrate()
    {
        $this->testQuestions = unserialize(session()->get('data'));
    }

    public function previousQuestion()
    {
        $this->question = $this->testQuestions->get($this->number - 2)->uuid;
        $this->setMainQuestion($this->question);
    }

    public function updateAnswer($questionId, $answer)
    {
//        if($this->mainQuestion instanceof MultipleChoiceQuestion) {
//            $answers = [];
//            $this->mainQuestion->multipleChoiceQuestionAnswers->pluck('id')->each(function ($i ,$key) use (&$answers, $answer) {
//                $answers[$i] =  $key == $answer ? 1 : 0;
//            });
//            $answer = $answers;
//        }
        $this->answers[$questionId]['answer'] = json_encode($answer);

        Answer::where('id', $this->answers[$questionId]['id'])
            ->update(['json' => json_encode($answer)]);
    }

    public function nextQuestion()
    {
        $this->question = $this->testQuestions->get($this->number)->uuid;
        $this->setMainQuestion($this->question);
    }

    public function render()
    {
        return view('livewire.student.test-take')->layout('layouts.app');
    }

    public function setMainQuestion($questionUuid)
    {
        $this->question = $questionUuid;
        $this->mainQuestion = Question::whereUuid($questionUuid)->first();
        $key = $this->testQuestions->search(function ($value, $key) use ($questionUuid) {
            return $value->uuid === $questionUuid;
        });
        $this->number = $key + 1;

        $this->emit('questionUpdated', $questionUuid, $this->getCurrentAnswer($questionUuid));
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
}
