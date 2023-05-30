<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use Illuminate\Support\Facades\Blade;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

class CompletionQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

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
        foreach ($this->answer as $key => $val) {
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val, false);
        }
        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
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
            $this->searchPattern,
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

                return sprintf('<select wire:model="answer.%s" class="form-input text-base disabled max-w-full overflow-ellipsis overflow-hidden" selid="testtake-select" disabled>%s</select>', $matches[1],
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

        return view('livewire.overview.completion-question', ['html' => $html]);
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
