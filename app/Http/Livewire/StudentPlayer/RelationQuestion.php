<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Answer;
use tcCore\Word;

abstract class RelationQuestion extends StudentPlayerQuestion
{
    public $uuid;
    public $answerStruct;
    public $answerStructOrder;
    public $answerText = [];

    public $words = [];

    public $viewStruct;

    public function mount(): void
    {
        $this->setAnswerStruct();
        $this->getWords();
        $this->createViewKeyStruct();
    }

    public function hydrate(): void
    {
        //Livewire resets the order every time the page is refreshed, so we need to set it again
        $this->answerStruct = collect($this->answerStructOrder)
            ->mapWithKeys(function ($wordId) {
                return [$wordId => $this->answerStruct[$wordId]];
            })
            ->toArray();
    }

    public function isQuestionFullyAnswered(): bool
    {
        return collect($this->answerStruct)->every(function ($answer) {
            return !empty($answer);
        });
    }

    protected function setAnswerStruct() : void
    {
        if ($this->hasGivenAnswer()) {
            $this->answerStruct = $this->getStructFromAnswer();
        } else {
            $this->setDefaultStruct();
            $this->saveEmptyAnswerStruct();
        }
        $this->answerStructOrder = array_keys($this->answerStruct);
    }

    /**
     * Split answerStruct keys in two halves (if uneven, first half is 1 longer),
     * and then merge them back together in the correct order, to show them in two columns. from top to bottom.
     * | 1 | 4 |
     * | 2 | 5 |
     * | 3 | - |
     */
    protected function createViewKeyStruct(): void
    {
        $this->viewStruct = collect($this->answerStruct)
            ->keys()
            ->split(2)
            ->each(function($item, $wordId) {
            $item->transform(function($wordId, $key) {
                $questionPrefixTranslation = !in_array($this->words[$wordId]['type'], ['subject', 'translation'])
                    ? __('question.word_type_'.$this->words[$wordId]['type'])
                    : null;
                return [
                    'answer' => null,
                    'question' => $this->words[$wordId]['text'],
                    'question_prefix' => $questionPrefixTranslation,
                    'wordId' => $wordId,
                ];
            });
        })->map->toArray();
    }

    protected function getWords(): void
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

    final protected function hasGivenAnswer(): bool
    {
        if($this instanceof Preview\RelationQuestion) return false;

        return !empty(json_decode($this->answers[$this->question->uuid]['answer'], true));
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
        if($this instanceof Preview\RelationQuestion) return false;

        return !!Answer::where('id', $this->answers[$this->question->uuid]['id'])
            ->update(['json' => $this->answerStruct]);
    }
}
