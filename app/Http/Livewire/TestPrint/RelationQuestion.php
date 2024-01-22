<?php

namespace tcCore\Http\Livewire\TestPrint;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Traits\WithCloseable;

class RelationQuestion extends TCComponent
{
    use WithCloseable;
    use WithGroups;

    public $question;

    public $answer = [];
    public $answerStruct = [];
    public $availableAnswersList = [];
    public $answerPlaceholdersList = [];
    public $answered;
    public $answers;
    public $attachment_counters;

    public $number;
    public $searchPattern = "/\[(\d+)\]/i";

    public function mount()
    {
        $this->setDefaultStruct();

        $this->createAnswerPlaceholdersList();

        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.test_print.relation-question');
    }

    /**
     * student answer json contains all the wordIds and not answered fields have a null value
     * array_filter removes all the null values
     */
    public function isQuestionFullyAnswered(): bool
    {
        return count($this->answer) === count(array_filter($this->answer));
    }

    /**
     * create a list of numbers
     */
    protected function createAnswerPlaceholdersList(): void
    {
        $iterator = 0;
        [$temp1, $temp2] = $this->answerStruct->split(2)
            ->map(function ($item) use (&$iterator) {
                return $item->mapWithKeys(function ($item, $key) use (&$iterator) {
                    $iterator++;
                    return [$iterator => $iterator];
                });
            });

        $this->answerPlaceholdersList = $temp1->zip($temp2)->flatten()->filter();
    }

    protected function setDefaultStruct(): void
    {
        $this->answerStruct = $this->question->getWordsForAnswerStruct()
            ->map(function ($word) {
                $word->prefix_text = !in_array($word->type->value, ['subject', 'translation'])
                    ? __('question.word_type_' . $word->type->value)
                    : '';

                return $word;
            });
    }
}
