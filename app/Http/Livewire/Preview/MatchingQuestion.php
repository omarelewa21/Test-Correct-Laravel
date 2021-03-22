<?php

namespace tcCore\Http\Livewire\Preview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Question;

class MatchingQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $answer;
    public $question;
    public $number;

    public $answers;
    public $answerStruct;

    public $shuffledAnswers;

    public function mount()
    {
        $this->question->loadRelated();

        foreach ($this->question->matchingQuestionAnswers as $key => $value) {
            if ($value->correct_answer_id !== null) {
                $this->answerStruct[$value->id] = "";
            }
        }
        $this->shuffledAnswers = $this->question->matchingQuestionAnswers->shuffle();
    }

    public function updateOrder($value)
    {
        $dbstring = [];
        foreach ($value as $key => $value) {
            if ($value['value'] == 'startGroep') {
                $value['value'] = '';
            }
            foreach ($value['items'] as $items) {
                $dbstring[$items['value']] = $value['value'];
            }
        }

        $this->answerStruct = $dbstring;

    }


    public function render()
    {
        return view('livewire.preview.matching-question');
    }

}
