<?php

namespace tcCore\Http\Livewire\CoLearning;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Livewire\Student\CoLearning;
use tcCore\Http\Traits\Questions\WithCompletionConversion;

class CompletionQuestion extends CoLearningQuestion
{
    use WithCompletionConversion;
    const SESSION_KEY = 'co-learning-answer-options';

    public array $answerOptions;
    public int $answerOptionsAmount;

    public $questionTextPartials;
    public $questionTextPartialFinal;


    public function render()
    {
        return view('livewire.co-learning.completion-question');
    }

    public function updatedAnswerOptions()
    {

        $this->answerOptions[$this->answerRatingId]['counts'] = [
            'score'           => $this->answerOptionsScore,
            'maxScore'        => $this->answerOptionsAmount,
            'amountCheckable' => $this->checkableAnswerOptionsAmount,
            'amountChecked'   => $this->checkedAnswerOptionsTrueOrFalseAmount,
        ];

        $this->writeAnswerOptionsToSession();

        $this->emit('UpdateAnswerRating', $this->answerOptions[$this->answerRatingId]['counts']);
    }

    public function isQuestionFullyAnswered(): bool
    {
        return collect($this->answer)->count() === $this->answerOptionsAmount;
    }

    protected function handleGetAnswerData(): void
    {
        $this->answer = (array)json_decode($this->answerRating->answer->json);

        $question_text = $this->answerRating->answer->question->converted_question_html;

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question_text);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();

        $this->createAnswerOptionsDataStructure();
    }

    public function getAnswerOptionsScoreProperty()
    {
        return collect($this->answerOptions[$this->answerRatingId]['answerOptions'])->reduce(function ($carry, $answerOption) {
            $carry += $answerOption['rating'] === '1' ? 1 : 0;
            return $carry;
        }, 0);
    }

    public function getCheckableAnswerOptionsAmountProperty()
    {
        return collect($this->answerOptions[$this->answerRatingId]['answerOptions'])->reduce(function ($carry, $answerOption) {
            $carry += $answerOption['answer'] !== null ? 1 : 0;
            return $carry;
        }, 0);
    }

    public function getCheckedAnswerOptionsTrueOrFalseAmountProperty()
    {
        return collect($this->answerOptions[$this->answerRatingId]['answerOptions'])->reduce(function ($carry, $answerOption) {
            $carry += $answerOption['rating'] !== null ? 1 : 0;
            return $carry;
        }, 0);
    }

    private function createAnswerOptionsDataStructure(): void
    {
        $this->answerOptionsAmount = $this->questionTextPartials->count();

        $this->getAnswerOptionsFromSession();

        if ($this->answerOptionsFromSessionAreOk()) {
            return;
        }

        for ($index = 0; $index < $this->answerOptionsAmount; $index++) {
            $this->answerOptions[$this->answerRatingId]['answerOptions'][] = [
                'rating'   => null,
                'answered' => isset($this->answer[$index]),
                'answer'   => $this->answer[$index] ?? null,
            ];
        }
        $this->writeAnswerOptionsToSession();
    }

    private function getAnswerOptionsFromSession()
    {
        if (session()->has(static::SESSION_KEY)) {
            $this->answerOptions = session()->get(static::SESSION_KEY);
        }
    }

    private function writeAnswerOptionsToSession()
    {
        session([static::SESSION_KEY => $this->answerOptions]);
    }

    /**
     * @return bool
     */
    private function answerOptionsFromSessionAreOk(): bool
    {
        return isset($this->answerOptions[$this->answerRatingId]['answerOptions']) &&
            collect($this->answerOptions[$this->answerRatingId]['answerOptions'])->count() === $this->answerOptionsAmount;
    }
}
