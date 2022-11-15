<?php

namespace tcCore\Http\Livewire\Questions\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithUpdatingHandling;

class MultipleSelectQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

    public $question;

    public $answer = '';

    public $answers;

    public $answerStruct;

    public $number;

    public $answerText;
    public $shuffledKeys;

    public function mount()
    {
        $this->selectable_answers = $this->question->selectable_answers;

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
        } else {
            $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
                $this->answerStruct[$answers->id] = 0;
            });
        }

        $this->shuffledKeys = array_keys($this->answerStruct);
        if (!$this->question->isCitoQuestion()) {
            if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse' && !$this->question->fix_order) {
                shuffle($this->shuffledKeys);
            }
        }

        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });
    }

    public function updatedAnswer($value)
    {
        if ($this->answerStruct[$value] === 1) {
            $this->answerStruct[$value] = 0;
        } else {
            $selected = count(array_keys($this->answerStruct, 1));
            if ($selected != $this->question->selectable_answers) {
                $this->answerStruct[$value] = 1;
            }
        }

        $json = json_encode($this->answerStruct);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->answer = '';
    }

    public function render()
    {
        return view('livewire.questions.question.multiple-select-question');
    }
}
