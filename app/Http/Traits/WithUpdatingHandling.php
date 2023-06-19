<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\Http\Requests\Request;
use tcCore\TestParticipant;

trait WithUpdatingHandling
{
    public function updatedWithUpdatingHandling(&$name, &$value)
    {
        if($this->skipFieldTransformation($name)) return;

        $refValue = $value;
        Request::filter($value);
        if($refValue !== $value) {
            $this->syncInput($name, $value);
        }
    }

    public function updateAnswerIdForTestParticipant()
    {
        $answer = Answer::select('id','test_participant_id')->whereId($this->answers[$this->question->uuid]['id'])->first();

        TestParticipant::whereId($answer->test_participant_id)->update(['answer_id' => $answer->getKey()]);
    }

    /**
     * Prevents the transformation of the given fields Or all fields if preventFieldTransformation property is true.
     * 
     * @param $name
     */
    private function skipFieldTransformation($name)
    {
        if(isset($this->preventFieldTransformation)){
            if(is_array($this->preventFieldTransformation)){
                return in_array($name, $this->preventFieldTransformation);
            }
            return is_bool($this->preventFieldTransformation) ? $this->preventFieldTransformation : false;
        }

        return false;
    }
}