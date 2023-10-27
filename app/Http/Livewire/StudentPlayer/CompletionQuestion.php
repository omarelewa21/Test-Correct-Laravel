<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\Questions\WithCompletionConversion;

abstract class CompletionQuestion extends StudentPlayerQuestion
{
    use WithCompletionConversion;

    public $options;
    public $questionTextPartials;
    public $questionTextPartialFinal;

    public function mount(): void
    {
        if(!$this->answer) {
            $this->buildAnswerStruct();
        }
        foreach ($this->answer as $key => $val) {
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val, false);
        }

        $this->setupQuestionData();
    }

    protected function setupQuestionData(): void
    {
        $question_text = $this->question->converted_question_html;
        $tags = [];

        foreach ($this->question->completionQuestionAnswers as $option) {
            $tags[$option->tag][$option->id] = $option->answer;
        }

        // Shuffle the options within each tag in original array
        foreach ($tags as &$tagOptions) {
            shuffle($tagOptions);
        }

        $this->options = $tags;

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question_text);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();
    }

    private function buildAnswerStruct(): void
    {
        foreach ($this->question->completionQuestionAnswers as $value) {
            $index = $this->question->isSubType('multi') ? $value->tag : (int)$value->tag - 1;
            $this->answer[$index] = "";
        }
    }
}
