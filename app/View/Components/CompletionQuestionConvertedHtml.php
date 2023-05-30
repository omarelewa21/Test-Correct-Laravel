<?php

namespace tcCore\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use tcCore\CompletionQuestion;
use tcCore\Question;

class CompletionQuestionConvertedHtml extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        private Question $question,
        private string $context = 'student',
        private ?Collection $answers = null,
        private ?int $completionQuestionTagCount = 0,        // User for teacher co-learning
    )
    {
        if (!$this->question->isType('completion')) {
            throw new \Exception('Question must be of type Completion and subtype ');
        }
    }

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
            'teacher-colearning' => $this->transformTextGapsForCoLearning(),
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
            $events = sprintf(
                '@blur="$refs.%s.scrollLeft = 0" @input="$event.target.setAttribute(\'title\', $event.target.value);"',
                'comp_answer_' . $tag_id
            );
            $context = $this->context;
            $question = $this->question;
            return view(
                'components.completion-question-converted-html',
                compact('tag_id', 'answer', 'events', 'rsSpan', 'context', 'question')
            );
        };
    }

    /**
     * Transform question text gaps for student
     *
     * @return \Closure
     */
    public function transformTextGapsForStudent()
    {
        return function ($matches) {
            $tag_id = $matches[1] - 1; // the completion_question_answers list is 1 based but the inputs need to be 0 based
            $events = '@blur="$el.scrollLeft = 0" @input="$event.target.setAttribute(\'title\', $event.target.value); $el.style.width = getInputWidth($el)"';
            $rsSpan = '';
            $answer = '';
            $context = $this->context;
            $question = $this->question;
            if (auth()->user()->text2speech) {
                $events = sprintf(
                    '@focus="handleTextBoxFocusForReadspeaker(event,\'%s\')" @blur="$el.scrollLeft = 0;handleTextBoxBlurForReadspeaker(event,\'%s\')" @input="$event.target.setAttribute(\'title\', $event.target.value); $el.style.width = getInputWidth($el)"',
                    $question->getKey(),
                    $question->getKey()
                );
                $rsSpan = '<span wire:ignore class="rs_placeholder"></span>';
            }
            return view(
                'components.completion-question-converted-html',
                compact('tag_id', 'answer', 'events', 'rsSpan', 'context', 'question')
            );
        };
    }

    /**
     * Transform question text gaps for teacher colearning
     *
     * @return \Closure
     */
    public function transformTextGapsForCoLearning()
    {
        return function ($matches) {
            $this->completionQuestionTagCount++;
            $tag_id = $matches[1];
            $events = '@input="$el.style.width = getInputWidth($el)"';
            $rsSpan = '';
            $question = $this->question;
            $answers = $this->answers;
            $answer = $answers?->where('tag', $tag_id)?->first()?->answer ?? '';
            $context = 'teacher-colearning';
            return view(
                'components.completion-question-converted-html',
                compact('tag_id', 'question', 'answer', 'events', 'rsSpan', 'context')
            );
        };
    }
}
