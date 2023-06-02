<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\TestTake;
use tcCore\Http\Livewire\StudentPlayer\OpenQuestion as AbstractOpenQuestion;

class OpenQuestion extends AbstractOpenQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;

    public $testTakeUuid;

    public function mount()
    {
        parent::mount();

        $this->setAnswer();
    }

    public function updatedAnswer($value)
    {
        $json = json_encode((object)['value' => $this->cleanData($value)]);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->emitTo('student-player.question.navigation', 'current-question-answered', $this->number);
    }

    public function render()
    {
        return view('livewire.student-player.question.open-question');
    }

    /**
     * filter answer value from xss and encode html entities
     *
     * @param string $value
     *
     * @return string
     */
    private function cleanData($value)
    {
        $value = clean($value);

        return $this->question->isSubType('short') ? strip_tags($value) : $value;
    }
}
