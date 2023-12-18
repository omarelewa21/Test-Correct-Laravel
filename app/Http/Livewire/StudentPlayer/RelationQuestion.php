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

    public $viewStruct;

    public function mount()
    {

        $this->setAnswerStruct();
        $this->getWords();

        $this->createViewKeyStruct();
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
    }

    /**
     * Split answerStruct keys in two halves (if uneven, first half is 1 longer),
     * and then merge them back together in the correct order, to show them in two columns. from top to bottom.
     * | 1 | 4 |
     * | 2 | 5 |
     * | 3 | - |
     */
    protected function createViewKeyStruct()
    {
        [$firstHalf, $secondHalf] = collect($this->answerStruct)
            ->keys()
            ->split(2);

        $answerStructMappedIntoColumns = $firstHalf
            ->zip($secondHalf)
            ->flatten(1)
            ->filter()
            ->flip()
            ->mapWithKeys(function ($null, $wordId) {
                $questionPrefixTranslation = !in_array($this->words[$wordId]['type'], ['subject', 'translation'])
                    ? __('question.word_type_'.$this->words[$wordId]['type'])
                    : null;
                return [
                    $wordId => [
                        'answer' => null,
                        'question' => $this->words[$wordId]['text'],
                        'question_prefix' => $questionPrefixTranslation,
                    ]
                ];
            })
            ->toArray();

        $this->viewStruct = $answerStructMappedIntoColumns;
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

    final protected function hasGivenAnswer(): bool
    {
        if($this instanceof Preview\RelationQuestion) return false;

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
        if($this instanceof Preview\RelationQuestion) return false;

        return !!Answer::where('id', $this->answers[$this->question->uuid]['id'])
            ->update(['json' => $this->answerStruct]);
    }
}
