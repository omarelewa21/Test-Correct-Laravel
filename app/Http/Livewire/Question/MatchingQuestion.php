<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class MatchingQuestion extends Component
{
    use WithAttachments, WithNotepad;

    public $answer;
    public $question;
    public $number;

    public $answers;
    public $answerStruct;

    public function mount()
    {
        $this->question->loadRelated();

        $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);

        if(!$this->answerStruct) {
            foreach($this->question->matchingQuestionAnswers as $key => $value) {
                if ($value->correct_answer_id !== null) {
                    $this->answerStruct[$value->id] = "";
                }
            }
        }
    }

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;
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

        $json = json_encode($dbstring);
        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']],
            ['question_id', $this->question->id],
        ])->update(['json' => $json]);

        $this->answerStruct = $dbstring;

        $this->dispatchBrowserEvent('current-question-answered');
    }


    public function render()
    {
        return view('livewire.question.matching-question');
    }

}
