<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\CompletionQuestion as AbstractCompletionQuestion;

class CompletionQuestion extends AbstractCompletionQuestion
{
    use WithGroups;

    public $answered;
    public $searchPattern = "/\[([0-9]+)\]/i";

    public function mount()
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        foreach ($this->answer as $key => $val) {
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val, false);
        }
        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    protected function multiHelper($createOptionCallback = null)
    {
        return parent::multiHelper(function ($matches, $answers) {
            return sprintf(
                '<select wire:model="answer.%s" class="form-input text-base disabled max-w-full overflow-ellipsis overflow-hidden" selid="testtake-select" disabled>%s</select>',
                $matches[1],
                $this->getOptions($answers)
            );
        });
    }

    public function render()
    {
        return view('livewire.student-player.overview.completion-question', ['html' => $this->getHtml()]);
    }

    public function isQuestionFullyAnswered(): bool
    {
        $tags = [];
        $this->question->completionQuestionAnswers->each(function ($answer) use (&$tags) {
            $tags[$answer->tag] = true;
        });
        return count($tags) === count(array_filter($this->answer));
    }
}
