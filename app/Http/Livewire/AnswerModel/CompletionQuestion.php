<?php

namespace tcCore\Http\Livewire\AnswerModel;

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
    public $answerStruct = [];
    public $answered;
    public $answers;

    public $number;
    public $searchPattern = "/\[([0-9]+)\]/i";

    public function mount()
    {
        $this->question->completionQuestionAnswers->each(function ($answer) {
            if($answer->correct){
                $this->answerStruct[$answer->tag] = $answer->answer;
                return true;
            }
            if(!array_key_exists($answer->tag,$this->answerStruct)){
                $this->answerStruct[$answer->tag] = '';
            }
        });


        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    private function completionHelper($question)
    {
        $question->getQuestionHtml();

        $question_text = $question->converted_question_html;

        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1]; // the completion_question_answers list is 1 based but the inputs need to be 0 based

            return sprintf(
                '<input value="%s" class="form-input mb-2 disabled truncate text-center overflow-ellipsis" type="text" id="%s" style="width: 100px" disabled/>',
                $this->answerStruct[$tag_id],
                'answer_' . $tag_id
            );
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
                return sprintf('<div class="overflow-ellipsis rounded-10" style="display: inline-flex;border: solid 2px #c3d0ed;"><ul style="list-style-type: none !important;">%s</ul></div>',
                    $this->getOptions($answers,$this->answerStruct[$matches[1]]));
            },
            $question_text
        );

        return $question_text;
    }

    private function getOptions($answers,$correct)
    {
        return collect($answers)->map(function ($option, $key) use ($correct) {
            $check = '';
            if(trim($option)==trim($correct)){
                $check = '<svg class="inline-block" width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                            <path class="stroke-current" stroke-width="3" d="M1.5 7.5l5 5 8-11" fill="none" fill-rule="evenodd" stroke-linecap="round"></path>
                        </svg>';
            }
            return sprintf('<li >%s %s</li>', $option,$check);
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

        return view('livewire.answer_model.completion-question', ['html' => $html]);
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
