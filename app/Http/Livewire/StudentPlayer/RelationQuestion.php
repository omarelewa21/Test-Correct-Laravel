<?php

namespace tcCore\Http\Livewire\StudentPlayer;

abstract class RelationQuestion extends StudentPlayerQuestion
{
    public $uuid;
    public $answerStruct;
    public $answerText = [];

    public function mount()
    {

        $this->setAnswerStruct();
//
//        $this->setAnswerTexts();
    }

    protected function setAnswerStruct() : void
    {
        if ($this->hasGivenAnswer()) {
            $this->answerStruct = $this->getStructFromAnswer();
        } else {
            $this->setDefaultStruct();
        }
    }

    /**
     * relation question answer struct:
     *  $this->answerStruct = [ 'word_id #1' => '(string) student answer', ... ]
     * @return void
     */
    final protected function setDefaultStruct(): void
    {
        //question id = 279

        if (
            $this->question->shuffle
            && !$this->question->shuffle_per_participant
            && $this instanceof Question\RelationQuestion
        ) {
            //preview and overview do not have a testTakeUuid
            // preview gets a random preview answer_struct
            // overview gets the answer_struct from the Question and should allready exist

            $this->answerStruct = $this->question->getAnswerStructFromTestTake($this->testTakeUuid);
            return;
        }

        //todo finaly set the answerStruct
        $this->answerStruct = $this->question->createAnswerStruct();
    }



    final protected function hasGivenAnswer(): bool
    {
        return !empty(json_decode($this->answers[$this->question->uuid]['answer']));
    }

    final protected function getStructFromAnswer(): array
    {
        $answer = $this->answers[$this->question->uuid];
//        [▼
//          "id" => 27
//          "uuid" => "efc59bc5-b385-45a5-8b39-0d9e9b801a50"
//          "order" => 1
//          "question_id" => 279
//          "answer" => null
//          "answered" => false
//          "closed" => 0
//          "closed_group" => 0
//          "group_id" => 0
//          "group_closeable" => 0
//        ]
        dd($answer);


        return json_decode($this->answers[$this->question->uuid]['answer'], true);
    }
}
