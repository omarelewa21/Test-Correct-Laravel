<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\MultipleChoiceQuestion;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\TestParticipant;
use tcCore\TestTake as Test;


class TestTake extends Component
{
    public $testTake;
    public $question = 1;
    protected $queryString = ['question'];
    public $content;

    public function mount(Test $testTake)
    {
        $this->testTake = $testTake;

        $content = $this->showQuestionAndAnswer(TestParticipant::where('user_id', Auth::id())->first(), Answer::where('id', 141)->first())->getOriginalContent();

        $this->content = collect($content);
        $this->content = $this->content['question'];
//        dd($this->content['question']);
    }

    public function render()
    {
        return view('student.test-take')->layout('layouts.app');
    }
    public function showQuestionAndAnswer(TestParticipant $testParticipant, Answer $question_but_it_is_answer_uuid)
    {
        $question = $question_but_it_is_answer_uuid->question;//real question

        $answer = Answer::where('test_participant_id', $testParticipant->getKey())
            ->where('question_id', $question->getKey())
            ->with('answerParentQuestions', 'Question', 'answerParentQuestions', 'answerParentQuestions.groupQuestion', 'answerParentQuestions.groupQuestion.attachments')
            ->first();

//        if ($answer && $answer->question instanceof QuestionInterface) {
//            $answer->question->loadRelated();
//        }

        $question->getQuestionInstance()->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function ($query) {
            $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
        }, 'pValue.educationLevel']);

        if ($question instanceof QuestionInterface) {
            $question->loadRelated();
        }

        if ($answer !== null) {
            // added as replacement of hearbeat input 20190830
            $testParticipant->setAttribute('answer_id', $answer->getKey());
            $testParticipant->setAttribute('heartbeat_at', Carbon::now());

            $testParticipant->save();
        }
        return Response::make([
            'answer' => $answer,
            'question' => $question
        ],
            200);
    }


}