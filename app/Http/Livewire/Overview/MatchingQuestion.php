<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class MatchingQuestion extends Component
{
    use WithAttachments, WithNotepad, WithCloseable;

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

        $this->shuffledAnswers = $this->question->matchingQuestionAnswers->shuffle();
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
}
