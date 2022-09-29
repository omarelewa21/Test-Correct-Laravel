<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Http\Traits\WithUpdatingHandling;
use tcCore\Question;
use Illuminate\Support\Str;

class MatchingQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

    public $answer;
    public $question;
    public $number;

    public $answers;
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

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;
    }

    private function matchingUpdateValueOrder($dbstring, $values){
        $databaseStruct = json_decode(
            Answer::find($this->answers[$this->question->uuid]['id'])->json,
            true);
        foreach ($values as $key => $value) {
            if ($value['value'] == 'startGroep') {
                $value['value'] = '';
            }

            foreach ($value['items'] as $items) {
                if(in_array($value['value'], $dbstring) && !is_null($databaseStruct)){
                    // value stored before in dbstring =>
                    $prevStoredKeyInDbstring = array_search($value['value'], $dbstring);        // Get previous key from dbstring
                    $prevStoredKeyInDatabase = array_search($value['value'], $databaseStruct);  // Get previous key from database

                    if($prevStoredKeyInDatabase == -1){
                        // value doesn't exist in database =>
                        $dbstring[$prevStoredKeyInDbstring] = '';        // set previous key in dbstring to empty string
                        $dbstring[$items['value']] = $value['value'];    // set new key to value
                    }else{
                        // value exists in database
                        $dbstring[$prevStoredKeyInDbstring] = $value['value']; // set previous key in dbstring to value
                        $dbstring[$items['value']] = '';                       // set new key to empty string
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
        if(Str::lower($this->question->subtype)  == "matching"){
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

        $json = json_encode($dbstring);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->answerStruct = $dbstring;

        $this->emitTo('question.navigation','current-question-answered', $this->number);
    }


    public function render()
    {
        return view('livewire.question.matching-question');
    }

}
