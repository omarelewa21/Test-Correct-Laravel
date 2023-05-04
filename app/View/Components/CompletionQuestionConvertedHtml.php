<?php

namespace tcCore\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use tcCore\CompletionQuestion;

class CompletionQuestionConvertedHtml extends Component
{

    public CompletionQuestion $question;
    public ?Collection $answers;
    public string $context;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(CompletionQuestion $question, string $context = 'student', ?Collection $answers = null)
    {
        $this->question = $question;
        $this->answers = $answers;
        $this->context = $context;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return string
     */
    public function render()
    {
        $question_text = $this->question->converted_question_html;
        $searchPattern = "/\[([0-9]+)\]/i";
        return preg_replace_callback($searchPattern, $this->transformTextGapsBasedOnContext(), $question_text);
    }

    /**
     * Transform question text gaps based on the context
     * 
     * @return \Closure
     */
    public function transformTextGapsBasedOnContext()
    {
        return match ($this->context) {
            'assessment' => $this->transformTextGapsForAssessment(),
            'preview' => $this->transformTextGapsForPreview(),
            default => $this->transformTextGapsForStudent(),
        };
    }

    /**
     * Transform question text gaps for assessment
     * 
     * @return \Closure
     */
    public function transformTextGapsForAssessment()
    {
        return function ($matches) {
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
    }
}
