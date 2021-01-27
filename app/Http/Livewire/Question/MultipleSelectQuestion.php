<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Question;

class MultipleSelectQuestion extends Component
{
    public $question;

    public $answer = '';

    public $answers;

    public $answerStruct;

    public $number;

    public $queryString = ['q'];

    public $q;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];


    public function mount()
    {
        $this->answer = collect((array) json_decode($this->answers[$this->question->uuid]['answer']))->search(function (
            $item
        ) {
            return $item == 1;
        });

        $this->answerStruct =
            array_fill_keys(
                array_keys(
                    array_flip(Question::whereUuid($this->question->uuid)
                        ->first()
                        ->multipleChoiceQuestionAnswers->pluck('id')
                        ->toArray()
                    )
                ), 0
            );
    }

    public function updatedAnswer($value)
    {
        $this->answerStruct = array_fill_keys(array_keys($this->answerStruct), 0);
        $this->answerStruct[$value] = 1;

        $json = json_encode($this->answerStruct);

        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']],
            ['question_id', $this->question->id],
        ])->update(['json' => $json]);


//        $this->emitUp('updateAnswer', $this->uuid, $this->answerStruct);
    }

    public function render()
    {
        return view('livewire.question.multiple-select-question');
    }
}
