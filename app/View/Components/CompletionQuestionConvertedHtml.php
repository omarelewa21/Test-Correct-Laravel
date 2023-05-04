<?php

namespace tcCore\View\Components;

use Illuminate\View\Component;
use tcCore\CompletionQuestion;

class CompletionQuestionConvertedHtml extends Component
{

    public CompletionQuestion $question;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(CompletionQuestion $question, string $context)
    {
        $this->question = $question;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $question_text = $this->question->converted_question_html;
        $searchPattern = "/\[([0-9]+)\]/i";
        $replacementFunction = function ($matches) {
            $tag_id = $matches[1];
            return sprintf(
                '<span class="inline-flex max-w-full">
                            <input class="form-input mb-2 truncate text-center overflow-ellipsis" 
                                    type="text" 
                                    id="%s" 
                                    style="width: 140px" 
                                    disabled
                            />
                        </span>',
                'answer_' . $tag_id,
            );
        };

        return preg_replace_callback($searchPattern, $replacementFunction, $question_text);
        return view('components.completion-question-converted-html');
    }
}
