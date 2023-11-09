<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\MatchingQuestion as AbstractMatchingQuestion;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithStudentPlayerOverview;

class MatchingQuestion extends AbstractMatchingQuestion
{
    use WithGroups;
    use WithAttachments;
    use WithStudentPlayerOverview;

    public $answered;
    private $skipMountStudentPlayerOverview;

    public function mount()
    {
        parent::mount();
        $this->answered = $this->answers[$this->question->uuid]['answered'];
        if ($this->answers[$this->question->uuid]['answer']) {
            $this->answer = true;
        }

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.student-player.overview.matching-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $givedAnswers = count(array_filter($this->answerStruct));
        $options = count($this->answerStruct);
        return $options === $givedAnswers;
    }
    
}
