<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use Illuminate\Support\Str;

abstract class MatchingQuestion extends StudentPlayerQuestion
{
    public $answerStruct;
    public $shuffledAnswers;

    public function mount()
    {
        $this->question->loadRelated();
        $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
        if(!$this->answerStruct) {
            foreach($this->question->matchingQuestionAnswers as $key => $value) {
                if ($value->correct_answer_id !== null) {
                    $this->answerStruct[$value->id] = "";
                }
            }
        }
        $this->shuffledAnswers = $this->question->matchingQuestionAnswers->shuffle();
    }

    protected function matchingUpdateValueOrder($dbstring, $values, $struct){
        foreach ($values as $key => $value) {
            if ($value['value'] == 'startGroep') {
                $value['value'] = '';
            }

            foreach ($value['items'] as $items) {
                if (in_array($value['value'], $dbstring) && !is_null($struct)) {
                    // value stored before in dbstring =>
                    $prevStoredKeyInDbstring = array_search($value['value'], $dbstring);        // Get previous key from dbstring
                    $prevStoredKeyInDatabase = array_search($value['value'], $struct);  // Get previous key from database

                    if ($prevStoredKeyInDatabase == -1) {
                        // value doesn't exist in database =>
                        $dbstring[$prevStoredKeyInDbstring] = '';        // set previous key in dbstring to empty string
                        $dbstring[$items['value']] = $value['value'];    // set new key to value
                    } else {
                        // value exists in database
                        $dbstring[$prevStoredKeyInDbstring] = $value['value']; // set previous key in dbstring to value
                        $dbstring[$items['value']] = '';                       // set new key to empty string
                    }
                } else {
                    // value is not previously stored in dbstring
                    $dbstring[$items['value']] = $value['value'];
                }
            }
        }
        return $dbstring;
    }

    public function updateOrder($values)
    {
        $dbstring = [];
        if (Str::lower($this->question->subtype) == "matching") {
            $dbstring = $this->matchingUpdateValueOrder($dbstring, $values);
        } else {
            foreach ($values as $key => $value) {
                if ($value['value'] == 'startGroep') {
                    $value['value'] = '';
                }
                foreach ($value['items'] as $items) {
                    $dbstring[$items['value']] = $value['value'];
                }
            }
        }
        return $dbstring;
    }
}
