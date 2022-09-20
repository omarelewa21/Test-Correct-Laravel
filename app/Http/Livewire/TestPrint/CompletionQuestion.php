<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithCloseable;

class CompletionQuestion extends Component
{
    use WithCloseable, WithGroups;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $question;

    public $answer = [];
    public $answerStruct = [];
    public $availableAnswersList = [];
    public $answerPlaceholdersList = [];
    public $answered;
    public $answers;
    public $attachment_counters;

    public $number;
    public $searchPattern = "/\[(\d+)\]/i";

    public function mount()
    {
        $this->question->completionQuestionAnswers->each(function ($answer) {
            if ($answer->correct) {
                $this->answerStruct[$answer->tag] = $answer->answer;
                return true;
            }
            if (!array_key_exists($answer->tag, $this->answerStruct)) {
                $this->answerStruct[$answer->tag] = '';
            }
        });
        $this->createAnswerPlaceholdersList();

        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    private function completionHelper($question)
    {
        //todo answerstruct contains the answers, and so it can be used to calculate the amount of lines. starts with 1 (not 0)
        // todo split up views of completion and selection? completely different

        $question->getQuestionHtml();

        $question_text = $question->converted_question_html;

        $replacementFunction = function ($matches) use ($question) {
            $tag_id = $matches[1]; // the completion_question_answers list is 1 based but the inputs need to be 0 based
            return sprintf('<span class="completion-question-placeholder"><strong>%s.</strong> .........</span>', $tag_id);
        };
        return preg_replace_callback($this->searchPattern, $replacementFunction, $question_text);
    }

    private function multiHelper($question)
    {
        if (empty($answerJson)) {
            $answerJson = [];
        }
        $question_text = $question->converted_question_html;

        $this->createAvailableAnswersList();

        $question_text = preg_replace_callback(
            $this->searchPattern,
            function ($matches) {
                return sprintf('<span class="completion-question-placeholder"><strong>%s.</strong> .........</span>', $matches[1]);
            },
            $question_text
        );

        return $question_text;
    }

    private function getOption($answers, $correct)
    {
        return collect($answers)->map(function ($option, $key) use ($correct) {
            if (trim($option) == trim($correct)) {
                $check = sprintf('<img class="icon_checkmark_pdf no-margin" src="data:image/svg+xml;charset=utf8,%s" >', $this->getEncodedCheckmarkSvg());
                return sprintf('<span class="overflow-ellipsis rounded-10 pdf-answer-model-select" >%s %s</span>', $option, $check);
            }
            return '';
        })->join('');
    }


    public function render()
    {
        if ($this->question->subtype == 'completion') {
            $html = $this->completionHelper($this->question);
            return view('livewire.test_print.completion-question', ['html' => $html]);
        } elseif ($this->question->subtype == 'multi') {
            $html = $this->multiHelper($this->question);
            return view('livewire.test_print.selection-question', ['html' => $html]);
        } else {
            throw new \Exception ('unknown type');
        }
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

    /**
     * create a list of numbers
     */
    private function createAnswerPlaceholdersList()
    {
        $answerStruct = collect($this->answerStruct);
        $max = $answerStruct->count();

        $left = collect([]);
        $right = collect([]);

        $answerStruct->each(function ($item, $i) use ($max, &$left, &$right) {
            return $i <= (int)round($max / 2) ? $left->add($i) : $right->add($i);
        });

        $this->answerPlaceholdersList = $left->zip($right)->flatten()->filter();
    }

    private function createAvailableAnswersList()
    {
        $availableAnswers = [];
        $this->question->completionQuestionAnswers->each(function ($option) use (&$availableAnswers) {
            $availableAnswers[$option->tag][] = $option->answer;
        });

        $this->availableAnswersList = collect($availableAnswers)->map(function ($answer) {
            return collect($answer)->shuffle()->mapWithKeys(function ($item, $key) {
                $asciiValueLetterA = 65;
                return [chr($asciiValueLetterA + $key) => $item];
            });
            return $answer;
        });
    }
}
