<?php

namespace tcCore\Http\Traits;

use tcCore\Question;

trait WithStudentPlayerOverview
{
    public function mountWithStudentPlayerOverview(): void
    {
        if(property_exists($this, 'skipMountStudentPlayerOverview')) return;

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function getIsQuestionLockedProperty(): bool
    {
        return $this->question->closeable 
            || ($this->question->groupQuestion && $this->question->groupQuestion->closeable);
    }
}