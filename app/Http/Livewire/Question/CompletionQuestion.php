<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class CompletionQuestion extends Component
{
    protected $listeners = ['questionUpdated' => '$refresh'];

    public $uuid;

    public $question;

    public $answers = [];

    private function helper()
    {
        $question = Question::whereUuid($this->uuid)->first();
        $this->question = $question->getQuestionHtml();

        $question_text = $question->getQuestionHTML();

        $searchPattern = "/\[([0-9]+)\]/i";
        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1] - 1; // the completion_question_answers list is 1 based but the inputs need to be 0 based

            return sprintf(
                '<input wire:model.lazy="answers.%d" class="form-input" type="text" id="%s" />',
                $tag_id,
                'answer_'.$tag_id
            );
        };

        $this->question = preg_replace_callback($searchPattern, $replacementFunction, $question_text);
    }

    public function render()
    {
        $this->helper();

        return view('livewire.question.completion-question', ['question' => $this->question]);
    }
}
