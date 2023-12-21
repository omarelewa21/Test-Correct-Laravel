<?php

namespace tcCore\Http\Livewire\TestPrint;

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
    public $availableAnswersList = [];
    public $answerPlaceholdersList = [];
    public $answered;
    public $answers;
    public $attachment_counters;

    public $number;
    public $searchPattern = "/\[(\d+)\]/i";

    public function mount()
    {
//        $this->question->completionQuestionAnswers->each(function ($answer) {
//            if ($answer->correct) {
//                $this->answerStruct[$answer->tag] = $answer->answer;
//                return true;
//            }
//            if (!array_key_exists($answer->tag, $this->answerStruct)) {
//                $this->answerStruct[$answer->tag] = '';
//            }
//        });

        // :question="$testQuestion"
        // :number="$questionFollowUpNumber++"
        // :test="$test"
        // :attachment_counters="$attachment_counters"

        //word->text and word->prefix_text are used in the view
        $this->answerStruct = $this->question->wordsToAsk()->keyBy('id')->map(function ($word) {
            $word->prefix_text = !in_array($word->type->value, ['subject', 'translation']) ? __('question.word_type_' . $word->type->value) : '';
            return $word;
        });

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
    protected function createAnswerPlaceholdersList()
    {
        $answerStruct = collect($this->answerStruct);
//        $max = $answerStruct->count();
//
//        $left = collect([]);
//        $right = collect([]);
//
//        $answerStruct->each(function ($item, $i) use ($max, &$left, &$right) {
//            return $i <= (int)round($max / 2) ? $left->add($i) : $right->add($i);
//        });
//
//        $this->answerPlaceholdersList = $left->zip($right)->flatten()->filter();

        $iterator = 0;
        [$temp1, $temp2] = $answerStruct->split(2)->map(function ($item) use (&$iterator) {
            return $item->mapWithKeys(function ($item, $key) use (&$iterator) {
                $iterator++;
                return [$iterator => $iterator];
            });
        });

        $this->answerPlaceholdersList = $temp1->zip($temp2)->flatten()->filter();
    }

}
