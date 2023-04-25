<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\Http\Requests\Request;
use tcCore\TestParticipant;

trait WithUpdatingHandling
{
    public function updatedWithUpdatingHandling(&$name, &$value)
    {
        if(!isset($this->preventAnswerTransformation) || !$this->preventAnswerTransformation) {
            $refValue = $value;
            Request::filter($value);
            if($refValue !== $value) {
                $this->syncInput($name, $value);
            }
        }

    }

    public function updateAnswerIdForTestParticipantWithUpdatingHandling()
    {
        $answer = Answer::select('id','test_participant_id')->whereId($this->answers[$this->question->uuid]['id'])->first();

        TestParticipant::whereId($answer->test_participant_id)->update(['answer_id' => $answer->getKey()]);
    }
}