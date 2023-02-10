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
    public $attachment_counters;

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
        if($this->question->subtype == 'Classify'){
            $this->answerOptions = collect($this->answerOptions)->filter(function ($value, $key) {
                return !empty($value) && $value != ' ';
            })->all();
        }

        return view('livewire.test_print.matching-question');
    }

    protected function matchingSubTypeHandler()
    {
        $this->shuffledAnswerSets = $this->question->matchingQuestionAnswers->mapToGroups(function ($item, $key) {
            return [$item->type => $item->answer];
        })->map(function ($group) {
            return $group->shuffle();
        });
    }

    protected function classifySubTypeHandler()
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

    protected function reorderAnswerOptions()
    {
        $count = $this->answerOptions->count();

        $left = collect([]);
        $right = collect([]);

        $this->answerOptions->each(function ($item, $i) use ($count, &$left, &$right) {
            $i+1 <= (int)round($count / 2)
                ? ($left[] = $i+1)
                : ($right[] = $i+1);
        });
        $numbers = $left->zip($right)->flatten()->filter();
        $this->answerOptions = $this->answerOptions->mapWithKeys(function ($option, $key) use ($numbers) {
            return [$numbers[$key] . '.' => $option];
        });
    }
}
