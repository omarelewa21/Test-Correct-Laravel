<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use Exception;
use Illuminate\Support\Facades\Blade;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

abstract class CompletionQuestion extends TCComponent
{
    use withCloseable;

    public $question;
    public $answer;
    public $answers;
    public $number;

    /**
     * @throws Exception
     */
    protected function completionHelper($context = 'student'): string
    {
        return Blade::renderComponent(new CompletionQuestionConvertedHtml($this->question, $context));
    }

    protected function multiHelper($createOptionCallback)
    {
        $question_text = $this->question->converted_question_html;
        $tags = [];

        foreach ($this->question->completionQuestionAnswers as $option) {
            $tags[$option->tag][$option->answer] = $option->answer;
        }

        return preg_replace_callback(
            '/\[([0-9]+)\]/i',
            function ($matches) use ($tags, $createOptionCallback) {
                $tag_id = $matches[1] - 1;
                $answers = $tags[$matches[1]];
                $keys = array_keys($answers);
                if (!$this->question->isCitoQuestion()) {
                    shuffle($keys);
                }
                $random = ['' => 'Selecteer'];
                foreach ($keys as $key) {
                    $random[$key] = $answers[$key];
                }

                return $createOptionCallback($matches, $random, $tag_id, $this->question);
            },
            $question_text
        );
    }

    protected function getOptions($answers)
    {
        return collect($answers)->map(function ($option, $key) {
            return sprintf('<option value="%s">%s</option>', $key, $option);
        })->join('');
    }

    protected function getHtml()
    {
        return  $this->question->isSubType('completion')
            ? $this->completionHelper()
            : $this->multiHelper();
    }
}
