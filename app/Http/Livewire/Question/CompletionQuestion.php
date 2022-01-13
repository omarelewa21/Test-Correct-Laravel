<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithUpdatingHandling;

class CompletionQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

    public $question;
    public $answer;
    public $answers;
    public $number;

    public function mount()
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
    }

    public function updatedAnswer($value, $field)
    {
        $this->answer[$field] = $value;

        $json = json_encode((object)$this->answer);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

    }

    private function completionHelper($question)
    {
        $question->getQuestionHtml();

        $question_text = $question->getQuestionHTML();

        $searchPattern = "/\[([0-9]+)\]/i";
        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1] - 1; // the completion_question_answers list is 1 based but the inputs need to be 0 based

            return sprintf(
                '<input spellcheck="false"    wire:model.lazy="answer.%d" class="form-input mb-2 truncate text-center overflow-ellipsis" type="text" id="%s" style="width: 120px" x-ref="%s" @focus="handleFocusForReadspeaker()" @blur="$refs.%s.scrollLeft = 0;handleBlurForReadspeaker()" @input="$event.target.setAttribute(\'title\', $event.target.value);" wire:key="%s"/>',
                $tag_id,
                'answer_' . $tag_id . '_' . $this->question->getKey(),
                'comp_answer_' . $tag_id,
                'comp_answer_' . $tag_id,
                'comp_answer_' . $tag_id
            );
        };

        return preg_replace_callback($searchPattern, $replacementFunction, $question_text);
    }

    private function multiHelper($question)
    {
        if (empty($answerJson)) {
            $answerJson = [];
        }

        $question_text = $question->getQuestionHtml();


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

                return sprintf('<select wire:model="answer.%s" class="form-input text-base max-w-full overflow-ellipsis overflow-hidden rs_clicklistenexclude"  @change="$event.target.setAttribute(\'title\', $event.target.value);" selid="testtake-select">%s</select>',
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

        return view('livewire.question.completion-question', ['html' => $html]);
    }
}
