<?php

namespace tcCore\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use tcCore\CompletionQuestion;

class CompletionQuestionConvertedHtml extends Component
{

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public CompletionQuestion $question,
        public string $context = 'student',
        public ?Collection $answers = null
    ){}

    /**
     * Get modfied string for completion question.
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
            'teacher-preview' => $this->transformTextGapsForPreview(),
            default => $this->transformTextGapsForAssessment(),
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

    /**
     * Transform question text gaps for teacher preview
     * 
     * @return \Closure
     */
    public function transformTextGapsForPreview()
    {
        return function ($matches) {
            $tag_id = $matches[1] - 1;      // the completion_question_answers list is 1 based but the inputs need to be 0 based
            $answer = '';
            $rsSpan = '';
            $events = sprintf('@blur="$refs.%s.scrollLeft = 0" @input="$event.target.setAttribute(\'title\', $event.target.value); $el.style.width = getInputWidth($el)"', 'comp_answer_' . $tag_id);
            $context = 'teacher-preview';
            $question = $this->question;
            return view('components.completion-question-converted-html', compact('tag_id', 'answer', 'events', 'rsSpan', 'context', 'question'));
        };
    }
}
