<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{
    use WithCloseable;

    public $question;

    public $queryString = ['q'];

    public $q;

    public $answer = '';
    public $answered;

    public $answers;

    public $answerStruct;
    public $shuffledKeys;

    public $number;

    public $arqStructure = [
        ['A', 'test_take.correct', 'test_take.correct', 'test_take.correct_reason'],
        ['B', 'test_take.correct', 'test_take.correct', 'test_take.incorrect_reason'],
        ['C', 'test_take.correct', 'test_take.incorrect', 'test_take.not_applicable'],
        ['D', 'test_take.incorrect', 'test_take.correct', 'test_take.not_applicable'],
        ['E', 'test_take.incorrect', 'test_take.incorrect', 'test_take.not_applicable'],
    ];

    public $answerText;

    public function mount()
    {
        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            $this->answer = array_keys($this->answerStruct, 1)[0];
        } else {
            $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
                $this->answerStruct[$answers->id] = 0;
            });
        }

        $this->shuffledKeys = array_keys($this->answerStruct);
        if (!$this->question->isCitoQuestion()) {
            if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse') {
                shuffle($this->shuffledKeys);
            }
        }

        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });

        $this->answered = $this->answers[$this->question->uuid]['answered'];
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
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.overview.arq-question');
        } elseif ($this->question->subtype == 'TrueFalse') {
            return view('livewire.overview.true-false-question');

        }

        return view('livewire.overview.multiple-choice-question');
    }
}
