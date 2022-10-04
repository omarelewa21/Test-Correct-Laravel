<?php

namespace tcCore\Http\Livewire\Preview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Question;
use Illuminate\Support\Str;

class MatchingQuestion extends Component
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $answer;
    public $question;
    public $testId;
    public $number;

    public $answers;
    public $answerStruct;

    public $shuffledAnswers;

    public function mount()
    {
        $this->question->loadRelated();

        foreach ($this->question->matchingQuestionAnswers as $key => $value) {
            if ($value->correct_answer_id !== null) {
                $this->answerStruct[$value->id] = "";
            }
        }
        $this->shuffledAnswers = $this->question->matchingQuestionAnswers->shuffle();
    }

    private function matchingUpdateValueOrder($dbstring, $values){
        foreach ($values as $key => $value) {
            if ($value['value'] == 'startGroep') {
                $value['value'] = '';
            }

            foreach ($value['items'] as $items) {
                if(in_array($value['value'], $dbstring) && !is_null($this->answerStruct)){
                    // value stored before in dbstring =>
                    $prevStoredKeyInDbstring = array_search($value['value'], $dbstring);        // Get previous key from dbstring
                    $prevStoredKeyInAnswerStruct = array_search($value['value'], $this->answerStruct);  // Get previous key from AnswerStruct

                    if($prevStoredKeyInAnswerStruct == -1){
                        // value doesn't exist in AnswerStruct =>
                        $dbstring[$prevStoredKeyInDbstring] = '';        // set previous key in dbstring to empty string
                        $dbstring[$items['value']] = $value['value'];    // set new key to value
                    }else{
                        // value exists in AnswerStruct
                        $dbstring[$prevStoredKeyInDbstring] = $value['value'];                 // set previous key in dbstring to empty string
                        $dbstring[$items['value']] = '';             // set new key to value
                    }
                }else{
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
        if(Str::lower($this->question->subtype) == "matching"){
            $dbstring = $this->matchingUpdateValueOrder($dbstring, $values);
        }
        else{
            foreach ($values as $key => $value) {
                if ($value['value'] == 'startGroep') {
                    $value['value'] = '';
                }
                foreach ($value['items'] as $items) {
                    $dbstring[$items['value']] = $value['value'];
                }
            }
        }

        $this->answerStruct = $dbstring;

    }


    public function render()
    {
        return view('livewire.preview.matching-question');
    }

}
