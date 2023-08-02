<?php

namespace tcCore\Http\Livewire\AnswerModel;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Traits\WithCloseable;

class CompletionQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

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
            if ($answer->correct) {
                $this->answerStruct[$answer->tag][] = $answer->answer;
//                return true;
            }
            if (!array_key_exists($answer->tag, $this->answerStruct)) {
                $this->answerStruct[$answer->tag] = [];
            }
        });


        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    private function completionHelper($question)
    {
        $question->getQuestionHtml();

        $question_text = $question->converted_question_html;
        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1]; // the completion_question_answers list is 1 based but the inputs need to be 0 based
            return collect($this->answerStruct[$tag_id])->map(function ($answerText) {
                return sprintf('<span class="form-input resize-none overflow-ellipsis rounded-10 pdf-answer-model-input" >%s </span>', $answerText);
            })->join('');
        };

        return preg_replace_callback($this->searchPattern, $replacementFunction, $question_text);
    }

    private function multiHelper($question)
    {
        $question_text = $question->converted_question_html;


        $tags = [];

        foreach ($question->completionQuestionAnswers as $option) {
            $tags[$option->tag][$option->answer] = $option->answer;
        }

        $question_text = preg_replace_callback(
            $this->searchPattern,
            function ($matches) use ($tags) {
                $answers = $tags[$matches[1]];
                return $this->getOption($answers, $this->answerStruct[$matches[1]][0]);
            },
            $question_text
        );

        return $question_text;
    }

    private function getOption($answers, $correct)
    {
        return collect($answers)
            ->filter(function ($option) use ($correct) {
                return trim($option) == trim($correct);
            })->map(function ($option) {
                return sprintf(
                    '<span class="overflow-ellipsis rounded-10 pdf-answer-model-select" >%s %s</span>',
                    $option,
                    sprintf(
                        '<img class="icon_checkmark_pdf no-margin" src="data:image/svg+xml;charset=utf8,%s" >',
                        $this->getEncodedCheckmarkSvg()
                    )
                );
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

    private function getEncodedCheckmarkSvg()
    {
        return rawurlencode('<svg width="13px" height="16px" viewBox="0 0 13 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <title>icons/checkmark small blue</title>
    <g id="icons/checkmark-small-blue" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="round">
        <polyline id="Path" stroke="#004DF5" stroke-width="3" points="1.5 7.5 5.5 11.5 11.5 3.5"></polyline>
    </g>
</svg>');
    }
}
