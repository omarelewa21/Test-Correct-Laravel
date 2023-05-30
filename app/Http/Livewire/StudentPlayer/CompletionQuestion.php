<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use tcCore\Answer;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

abstract class CompletionQuestion extends TCComponent
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $question;
    public $answer;
    public $answers;
    public $number;
    public $preventAnswerTransformation = true;
    public $testTakeUuid;

    public function mount()
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        foreach ($this->answer as $key => $val) {
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val, false);
        }
    }

    public function updatedAnswer($value, $field)
    {
        $this->answer[$field] = $value;

        $data = $this->answer;

        if ($this->isOfType('completion')) {
            $value = BaseHelper::transformHtmlChars($value);
            $data[$field] = $value;
        }
        $json = json_encode((object)$data);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

    }

    private function completionHelper($question)
    {
        return Blade::renderComponent(new CompletionQuestionConvertedHtml($question, $context='student'));
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
            function ($matches) use ($tags, $isCitoQuestion, $question) {
                $tag_id = $matches[1] - 1;
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
                $events = '@change="$event.target.setAttribute(\'title\', $event.target.value);"';
                $rsSpan = '';
                if (Auth::user()->text2speech) {
                    $events = sprintf('@change="$event.target.setAttribute(\'title\', $event.target.value);" @focus="rsFocusSelect(event,\'%s\',\'%s\')" @blur="rsBlurSelect(event,\'%s\')"', 'comp_answer_' . $tag_id, $question->getKey(), $question->getKey());
                    $rsSpan = '<span wire:ignore class="rs_placeholder"></span>';
                }
                return sprintf('<span class="completion-response-object-container"><select wire:model="answer.%s" class="form-input text-base max-w-full overflow-ellipsis overflow-hidden rs_clicklistenexclude"  %s selid="testtake-select" x-ref="%s">%s</select>%s</span>',
                    $matches[1],
                    $events,
                    'select_answer_' . $tag_id,
                    $this->getOptions($answers),
                    $rsSpan
                );

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

    public function isOfType($type)
    {
        return $this->question->subtype == $type;
    }

    public function render()
    {
        if ($this->isOfType('completion')) {
            $html = $this->completionHelper($this->question);
        } elseif ($this->isOfType('multi')) {
            $html = $this->multiHelper($this->question);
        } else {
            throw new \Exception ('unknown type');
        }

        return view('livewire.question.completion-question', ['html' => $html]);
    }
}
