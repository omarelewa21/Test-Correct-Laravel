<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use Livewire\Component;
use tcCore\Question;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithCloseable;

class CompletionQuestion extends Component
{
    use WithCloseable;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $question;

    public $answer = [];
    public $answered;
    public $answers;

    public $number;
    public $searchPattern = "/\[([0-9]+)\]/i";

    public function mount()
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        foreach($this->answer as $key => $val){
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val);
        }
        $this->answered = $this->answers[$this->question->uuid]['answered'];
        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    private function completionHelper($question)
    {
        $question->getQuestionHtml();

        $question_text = $question->converted_question_html;

        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1] - 1; // the completion_question_answers list is 1 based but the inputs need to be 0 based
            return sprintf('<span class="form-input resize-none overflow-ellipsis rounded-10 pdf-answer-model-input" >%s </span>', $this->answer[$tag_id]);
        };

        return preg_replace_callback($this->searchPattern, $replacementFunction, $question_text);
    }

    private function multiHelper($question)
    {
        if (empty($answerJson)) {
            $answerJson = [];
        }

        $question_text = $question->converted_question_html;


        $tags = [];

        foreach ($question->completionQuestionAnswers as $option) {
            $tags[$option->tag][$option->answer] = $option->answer;
        }

        $question_text = preg_replace_callback(
            $this->searchPattern,
            function ($matches) use ($tags) {

                $answers = $tags[$matches[1]];
                $keys = array_keys($answers);
                $random = array(
                    '' => 'Selecteer'
                );
                foreach ($keys as $key) {
                    $random[$key] = $answers[$key];
                }

                $answers = $random;
                if(array_key_exists($matches[1],$this->answer)){
                    return $this->getOption($answers,$this->answer[$matches[1]]);
                }
                return '<span class="overflow-ellipsis rounded-10 pdf-answer-model-select" ></span>';

//                return sprintf('<select wire:model="answer.%s" class="form-input text-base disabled max-w-full overflow-ellipsis overflow-hidden" selid="testtake-select" disabled>%s</select>', $matches[1],
//                    $this->getOptions($answers));

//                return $this->Form->input('Answer.'.$tag_id ,['id' => 'answer_' . $tag_id, 'class' => 'multi_selection_answer', 'onchange' => 'Answer.answerChanged = true', 'value' => $value, 'options' => $answers, 'label' => false, 'div' => false, 'style' => 'display:inline-block; width:150px']);
            },
            $question_text
        );

        return $question_text;
    }

    private function getOption($answers,$correct)
    {
        return collect($answers)->map(function ($option, $key) use ($correct) {
            if(trim($option)==trim($correct)){
                $check = sprintf('<img class="icon_checkmark_pdf no-margin" src="data:image/svg+xml;charset=utf8,%s" >',$this->getEncodedCheckmarkSvg());
                return sprintf('<span class="overflow-ellipsis rounded-10 pdf-answer-model-select" >%s %s</span>', $option,$check);
            }
            return '';
        })->join('');
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

        return view('livewire.test_take_overview_preview.completion-question', ['html' => $html]);
    }

    public function isQuestionFullyAnswered(): bool
    {
        $tags = [];
        $this->question->completionQuestionAnswers->each(function ($answer) use (&$tags) {
            $tags[$answer->tag] = true;
        });
        return count($tags) === count(array_filter($this->answer));
    }
}
