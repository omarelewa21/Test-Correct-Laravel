<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\Teacher\Questions\CmsBase;
use tcCore\Http\Requests\Request;
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
    public $preventAnswerTransformation = true;

    public function mount()
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        foreach($this->answer as $key => $val){
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val);
        }
    }

    public function updatedAnswer($value, $field)
    {
        $this->answer[$field] = $value;

        $data = $this->answer;

        if($this->isOfType('completion')){
            $value = BaseHelper::transformHtmlChars($value);
            $data[$field] = $value;
        }
        $json = json_encode((object)$data);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

    }

    private function completionHelper($question)
    {
        $question->getQuestionHtml();

        $question_text = $question->getQuestionHTML();

        $searchPattern = "/\[([0-9]+)\]/i";
        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1] - 1; // the completion_question_answers list is 1 based but the inputs need to be 0 based
            $events = sprintf('@blur="$refs.%s.scrollLeft = 0" @input="$event.target.setAttribute(\'title\', $event.target.value);"','comp_answer_' . $tag_id);
            if(Auth::user()->text2speech){
                $events = sprintf('@mouseup="handleMouseupForReadspeaker(event,this)" @focus="handleTextBoxFocusForReadspeaker(event,\'%s\')" @blur="$refs.%s.scrollLeft = 0;handleTextBoxBlurForReadspeaker(event,\'%s\')" @input="$event.target.setAttribute(\'title\', $event.target.value);"',$question->getKey(),'comp_answer_' . $tag_id,$question->getKey());
            }
            return sprintf(
                '<input spellcheck="false"    wire:model.lazy="answer.%d" class="form-input mb-2 truncate text-center overflow-ellipsis" type="text" id="%s" style="width: 120px" x-ref="%s" %s wire:key="%s"/>',
                $tag_id,
                'answer_' . $tag_id . '_' . $this->question->getKey(),
                'comp_answer_' . $tag_id,
                $events,
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
            function ($matches) use ($tags, $isCitoQuestion,$question) {
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
                if(Auth::user()->text2speech){
                    $events = sprintf('@change="$event.target.setAttribute(\'title\', $event.target.value);" @focus="rsFocusSelect(event,\'%s\',\'%s\')" @blur="rsBlurSelect(\'%s\')"','comp_answer_' . $tag_id,$question->getKey(),$question->getKey());
                }
                return sprintf('<select wire:model="answer.%s" class="form-input text-base max-w-full overflow-ellipsis overflow-hidden rs_clicklistenexclude"  %s selid="testtake-select" x-ref="%s">%s</select>',
                    $matches[1],
                    $events,
                    'select_answer_' . $tag_id,
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
