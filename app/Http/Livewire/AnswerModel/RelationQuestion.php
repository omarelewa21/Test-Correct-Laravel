<?php

namespace tcCore\Http\Livewire\AnswerModel;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Traits\WithCloseable;

class RelationQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

    public $question;

    public $answer = [];
    public $answerStruct = [];
    public $answered;
    public $answers;

    public $number;

    public function mount()
    {

        $this->answerStruct = $this->question
            ->wordsToAsk()
            ->keyBy('id')
            ->map(function ($word) {
                $word->answer = $word->correctAnswerWord()->text;
//                $word->prefix_text = !in_array($word->type->value, ['subject', 'translation']) ? __('question.word_type_' . $word->type->value) : '';
                $word->prefix_text = !in_array($word->type->value, ['subject']) ? __('question.word_type_' . $word->type->value) : '';
//                $word->prefix_text = __('question.word_type_' . $word->type->value);
                return $word;
            });


        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }


    public function render()
    {
        return view('livewire.answer_model.relation-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        //this is the answer model, so why is this method here?
        return true;
    }
}
