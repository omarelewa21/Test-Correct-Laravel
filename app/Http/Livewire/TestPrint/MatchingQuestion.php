<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class MatchingQuestion extends Component
{
    use  WithNotepad, WithCloseable, WithGroups;

    public $answer;
    public $answered;
    public $question;
    public $number;

    public $answers;
    public $answerOptions;
    public $answerGroups;
    public $answerStruct = [];

    public $shuffledAnswerSets;

    public function mount()
    {

        $this->question->loadRelated();

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
        if($this->question->subtype == "Matching") {
            $this->matchingSubTypeHandler();
        } elseif ($this->question->subtype == "Classify") {
            $this->classifySubTypeHandler();
        }
    }

    public function render()
    {
        return view('livewire.test_print.matching-question');
    }

    private function matchingSubTypeHandler()
    {
        $this->shuffledAnswerSets = $this->question->matchingQuestionAnswers->mapToGroups(function ($item, $key) {
            return [$item->type => $item->answer];
        })->map(function ($group) {
            return $group->shuffle();
        });
    }

    private function classifySubTypeHandler()
    {
        [$this->answerGroups, $this->answerOptions] = $this->question->matchingQuestionAnswers->mapToGroups(function ($item, $key) {
            return [$item->type => $item->answer];
        })->map(function ($group, $key) {
            if($key == "RIGHT"){
                return $group->shuffle();
            }
            return $group;
        })->chunk(1)->map->flatten();

        $this->reorderAnswerOptions();
    }

    private function reorderAnswerOptions()
    {
//        dd($this->answerOptions->count(), $this->answerOptions);
        //todo reorder answerOptions so the keys render like:
        // 1 4
        // 2 5
        // 3
        $count = $this->answerOptions->count();

        $left = collect([]);
        $right = collect([]);

        $this->answerOptions->each(function ($item, $i) use ($count, &$left, &$right) {
            return $i+1 <= (int)round($count / 2) ? ($left[(string)($i+1)] = $item) : ($right[(string)($i+1)] = $item);
        });
        dd($left, $right);
        $this->answerOptions = $left->zip($right)->flatten()->filter();
        dd($this->answerOptions);
    }
}
