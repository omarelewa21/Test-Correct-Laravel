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

    public function mount(): void
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        parent::mount();
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

    public function render()
    {
        return view('livewire.student-player.question.completion-question');
    }
}
