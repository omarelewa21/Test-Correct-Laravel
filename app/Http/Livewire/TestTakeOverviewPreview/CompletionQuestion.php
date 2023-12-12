<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithCloseable;

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

    public $showQuestionText;

    public function mount()
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        foreach ($this->answer as $key => $val) {
            $this->answer[$key] = BaseHelper::transformHtmlCharsReverse($val);
        }
        $this->answered = $this->answers[$this->question->uuid]['answered'];
        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    private function createAnswersHtml($zeroBasedArray = false)
    {
        $result = '';
        foreach($this->answer as $tag => $val) {
            $tagNumber = $zeroBasedArray
                ? $tag + 1
                : $tag;

            $result .= sprintf(
                '<span class="pdf-student-answers-label">%s. </span> <span class="pdf-student-answers-input" >%s </span><br>',
                $tagNumber,
                $val
            );
        }
        return $result;
    }

//    private function completionHelper($question)
//    {
//        $result = '';
//        foreach($this->answer as $key => $val) {
//            $result .= sprintf('<span class="pdf-student-answers-label">%s. </span> <span class="pdf-student-answers-input" >%s </span><br>', $key + 1, $val);
//        }
//        return $result;
//    }
//
//    private function multiHelper($question)
//    {
//        $result = '';
//
//        foreach($this->answer as $key => $val) {
//            $result .= sprintf('<span class="pdf-student-answers-label">%s. </span> <span class="pdf-student-answers-input" >%s </span><br>', $key, $val);
//        }
//        return $result;
//    }

//    private function getOption($answers, $correct)
//    {
//        $iterator = 0;
//        return collect($answers)->map(function ($option, $key) use ($correct, &$iterator) {
//            //correct is a strange name, it is the answer that is given by the user
//            if($correct == '' && $iterator == 0) {
//                $iterator++;
//                return '<span class="overflow-ellipsis rounded-10 pdf-answer-model-select" >&nbsp;&nbsp;&nbsp;&nbsp;</span>';
//            }
//
//            if (trim($option) == trim($correct)) {
//                $check = sprintf('<img class="icon_checkmark_pdf no-margin" src="data:image/svg+xml;charset=utf8,%s" >', $this->getEncodedCheckmarkSvg());
//                return sprintf('<span class="overflow-ellipsis rounded-10 pdf-answer-model-select" >%s %s</span>', $option, $check);
//            }
//            return '';
//        })->join('');
//    }
//
//    private function getOptions($answers)
//    {
//        return collect($answers)->map(function ($option, $key) {
//            return sprintf('<option value="%s">%s</option>', $key, $option);
//        })->join('');
//    }

    public function render()
    {
        if ($this->question->subtype == 'completion') {
            $html = $this->createAnswersHtml(
                zeroBasedArray: true
            );

        } elseif ($this->question->subtype == 'multi') {
            $html = $this->createAnswersHtml(
                zeroBasedArray: false
            );
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
