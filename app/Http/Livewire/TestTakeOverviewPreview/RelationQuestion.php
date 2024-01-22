<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Word;

class RelationQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

    public $question;

    public $answer = [];
    public $answered;
    public $answers;

    public $number;

    public $showQuestionText;

    public $words;

    public function mount()
    {
//            :question="$testQuestion"
//            :number="++$key"
//            :answers="$answers"
//            :showQuestionText="$showQuestionText"

        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        foreach ($this->answer as $key => $val) {
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val);
        }
        $this->words = Word::whereIn('id', array_keys($this->answer))->get()->keyBy('id');

        //answer $word->answer
        //word text $word->text
        //word prefix text $word->prefix_text
        $this->words->transform(function ($word) {
            $word->answer = $this->answer[$word->id];
//            $word->prefix_text = !in_array($word->type->value, ['subject', 'translation']) ? __('question.word_type_'.$word->type->value) : '';
            $word->prefix_text = !in_array($word->type->value, ['subject']) ? __('question.word_type_'.$word->type->value) : '';
            return $word;
        });

        $this->answered = $this->answers[$this->question->uuid]['answered'];
        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        $html = '';

        return view('livewire.test_take_overview_preview.relation-question', ['html' => $html]);
    }

    /**
     * student answer json contains all the wordIds and not answered fields have a null value
     * array_filter removes all the null values
     */
    public function isQuestionFullyAnswered(): bool
    {
        return count($this->answer) === count(array_filter($this->answer));
    }
}
