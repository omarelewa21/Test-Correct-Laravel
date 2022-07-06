<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\MatchingQuestionAnswer;
use tcCore\Question;

class MatchingQuestion extends Component
{
    use WithAttachments, WithNotepad, WithCloseable, WithGroups;

    public $answer;
    public $answered;
    public $question;
    public $number;

    public $answers;
    public $answerStruct;

    public $shuffledAnswers;

    public function mount()
    {
        $this->question->loadRelated();
        $this->answered = $this->answers[$this->question->uuid]['answered'];

        $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);

        if ($this->answers[$this->question->uuid]['answer']) {
            $this->answer = true;
        }

        if(!$this->answerStruct) {
            foreach($this->question->matchingQuestionAnswers as $key => $value) {
                if ($value->correct_answer_id !== null) {
                    $this->answerStruct[$value->id] = "";
                }
            }
        }
//        dump($this->question->getKey());
//        dump($this->answerStruct);

        //$this->shuffledAnswers = $this->question->matchingQuestionAnswers->shuffle();
        $this->shuffledAnswers = $this->getMatchingQuestionAnswers();
//        dump($this->shuffledAnswers->toArray());

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.overview.matching-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $givedAnswers = count(array_filter($this->answerStruct));
        $options = count($this->answerStruct);
        return $options === $givedAnswers;
    }

    private function getMatchingQuestionAnswers()
    {
        $matchingQuestionAnswersIds = [];
        $matchingQuestionAnswers = [];
        collect($this->answerStruct)->each(function($key,$value) use ($matchingQuestionAnswersIds){
            $matchingQuestionAnswersIds[] = $key;
            $matchingQuestionAnswersIds[] = $value;
        });
        array_unique($matchingQuestionAnswersIds);
        collect($matchingQuestionAnswersIds)->each(function($key,$value) use ($matchingQuestionAnswers){
            $matchingQuestionAnswer = MatchingQuestionAnswer::find($value);
            $matchingQuestionAnswers[] = $matchingQuestionAnswer;
        });
        return $matchingQuestionAnswers;
    }
}
