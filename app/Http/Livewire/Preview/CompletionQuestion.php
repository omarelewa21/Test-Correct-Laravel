<?php

namespace tcCore\Http\Livewire\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class CompletionQuestion extends TCComponent
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $question;
    public $testId;
    public $answer;
    public $answers;
    public $number;


    public function updatedAnswer($value, $field)
    {

    }

    private function completionHelper($question)
    {
        $question->getQuestionHtml();

        $question_text = $question->converted_question_html;

        $searchPattern = "/\[([0-9]+)\]/i";
        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1] - 1; // the completion_question_answers list is 1 based but the inputs need to be 0 based
            $answer = '';
            $rsSpan = '';
            $events = sprintf('@blur="$refs.%s.scrollLeft = 0" @input="$event.target.setAttribute(\'title\', $event.target.value); $el.style.width = getInputWidth($el)"', 'comp_answer_' . $tag_id);
            $context = 'teacher-preview';
            return view('livewire.teacher.co-learning-completion-question-html', compact('tag_id', 'question', 'answer', 'events', 'rsSpan', 'context'));
        };

        return preg_replace_callback($searchPattern, $replacementFunction, $question_text);
    }

    private function multiHelper($question)
    {
        $question_text = $question->converted_question_html;


        $tags = [];

        foreach ($question->completionQuestionAnswers as $option) {
            $tags[$option->tag][$option->answer] = $option->answer;
        }
        $isCitoQuestion = $question->isCitoQuestion();

        $question_text = preg_replace_callback(
            '/\[([0-9]+)\]/i',
            function ($matches) use ($tags, $isCitoQuestion) {

                $answers = $tags[$matches[1]];
                $keys = array_keys($answers);
                if (!$isCitoQuestion) {
                    shuffle($keys);
                }
                $random = array(
                    '' => 'Selecteer'
                );
                foreach ($keys as $key) {
                    $random[$key] = $answers[$key];
                }

                $answers = $random;

                return sprintf('<select wire:model="answer.%s" class="form-input text-base max-w-full overflow-ellipsis overflow-hidden" @change="$event.target.setAttribute(\'title\', $event.target.value);" selid="testtake-select">%s</select>',
                    $matches[1],
                    $this->getOptions($answers));

//                return $this->Form->input('Answer.'.$tag_id ,['id' => 'answer_' . $tag_id, 'class' => 'multi_selection_answer', 'onchange' => 'Answer.answerChanged = true', 'value' => $value, 'options' => $answers, 'label' => false, 'div' => false, 'style' => 'display:inline-block; width:150px']);
            },
            $question_text
        );

        return $question_text;
    }

    private function getOptions($answers)
    {
        return collect($answers)->map(function ($option, $key) {
            return sprintf('<option value="%s">%s</option>', $key, $option);
        })->join('');
    }

    public function render()
    {
        if ($this->question->subtype == 'completion') {
            $html = $this->completionHelper($this->question);
        } elseif ($this->question->subtype == 'multi') {
            $html = $this->multiHelper($this->question);
        } else {
            throw new \Exception ('unknown type');
        }

        return view('livewire.preview.completion-question', ['html' => $html]);
    }
}
