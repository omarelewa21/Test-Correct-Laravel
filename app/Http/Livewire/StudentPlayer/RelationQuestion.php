<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Answer;
use tcCore\Word;

abstract class RelationQuestion extends StudentPlayerQuestion
{
    public $uuid;
    public $answerStruct;
    public $answerText = [];

    public $words = [];
    public $firstHalfAnswerStruct;
    public $secondHalfAnswerStruct;

    public function mount()
    {

        $this->setAnswerStruct();
        $this->getWords();

        $this->createViewDataStruct();
//        $this->setAnswerTexts();
    }

    protected function setAnswerStruct() : void
    {
        if ($this->hasGivenAnswer()) {
            $this->answerStruct = $this->getStructFromAnswer();
        } else {
            $this->setDefaultStruct();
            $this->saveEmptyAnswerStruct();
        }
    }

    protected function createViewDataStruct()
    {
        //todo wip
//        return;
        $totalLength = count($this->answerStruct);
        $firstHalfLength = (int) ceil(count($this->answerStruct)/2);

        $answerStructKeys = array_keys($this->answerStruct);

        for($i = 0; $i < $totalLength; $i++) {
            if ($i < $firstHalfLength) {
                $this->firstHalfAnswerStruct[$answerStructKeys[$i]] = $this->answerStruct[$answerStructKeys[$i]];
            } else {
                $this->secondHalfAnswerStruct[$answerStructKeys[$i]] = $this->answerStruct[$answerStructKeys[$i]];
            }
        }

//        dd($this->firstHalfAnswerStruct, $this->secondHalfAnswerStruct, $this->answerStruct);
    }

    protected function getWords()
    {
        $this->words = Word::whereIn('id', array_keys($this->answerStruct))->get()->keyBy('id')->toArray();
    }

    /**
     * relation question answer struct:
     *  $this->answerStruct = [ 'word_id #1' => '(string) student answer', ... ]
     * @return void
     */
    final protected function setDefaultStruct(): void
    {
        if ($this->shouldGetAnswerStructFromTestTake()) {
            $this->answerStruct = $this->question->getAnswerStructFromTestTake($this->testTakeUuid);
            return;
        }

        $this->answerStruct = $this->question->createAnswerStruct();
    }

    /**
     * If RelationQuestion words should be shuffled, but be the same for the entire TestTake,
     * then get the answer_struct from the TestTakeRelationQuestion.
     * @return bool
     */
    final protected function shouldGetAnswerStructFromTestTake(): bool
    {
        return $this->question->shuffle
            && !$this->question->shuffle_per_participant
            && $this instanceof Question\RelationQuestion;
    }

    public function updatedAnswerStruct($value)
    {
        // todo implement? or not needed?
    }

    final protected function hasGivenAnswer(): bool
    {
        return !empty(json_decode($this->answers[$this->question->uuid]['answer']));
    }

    final protected function getStructFromAnswer(): array
    {
        return json_decode($this->answers[$this->question->uuid]['answer'], true);
    }

    /**
     * Save empty answer struct to answer without setting Answer to 'done'
     * @return bool
     */
    final protected function saveEmptyAnswerStruct(): bool
    {
        return !!Answer::where('id', $this->answers[$this->question->uuid]['id'])
            ->update(['json' => $this->answerStruct]);
    }
}
