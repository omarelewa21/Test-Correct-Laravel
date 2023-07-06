<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use Exception;
use Illuminate\Support\Facades\Blade;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\Questions\WithCompletionConversion;
use tcCore\Http\Traits\WithCloseable;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

abstract class CompletionQuestion extends TCComponent
{
    use withCloseable;
    use WithCompletionConversion;

    public $question;
    public $answer;
    public $answers;
    public $number;
    public $options;
    public $questionTextPartials;
    public $questionTextPartialFinal;

    public function mount(): void
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        if(!$this->answer) {
            foreach($this->question->completionQuestionAnswers as $key => $value) {
                $this->answer[$key] = "";
            }
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

        $this->options = $tags;

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question_text);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();
    }
}
