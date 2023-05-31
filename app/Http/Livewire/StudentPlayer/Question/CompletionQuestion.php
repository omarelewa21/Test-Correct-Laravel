<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use Illuminate\Support\Facades\Auth;
use tcCore\Answer;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Livewire\StudentPlayer\CompletionQuestion as AbstractCompletionQuestion;

class CompletionQuestion extends AbstractCompletionQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;

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

        if ($this->question->isSubType('completion')) {
            $value = BaseHelper::transformHtmlChars($value);
            $data[$field] = $value;
        }
        $json = json_encode((object)$data);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);
    }

    protected function multiHelper($createOptionCallback = null)
    {
        return parent::multiHelper(function ($matches, $answers, $tag_id, $question) {
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
        });
    }

    public function render()
    {
        return view('livewire.student-player.question.completion-question', ['html' => $this->getHtml()]);
    }
}
