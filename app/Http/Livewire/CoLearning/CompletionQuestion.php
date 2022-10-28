<?php

namespace tcCore\Http\Livewire\CoLearning;

use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Livewire\Student\CoLearning;

class CompletionQuestion extends CoLearningQuestion
{
    const SEARCH_PATTERN = "/\[([0-9]+)\]/i";

    public array $answerOptions;
    public int $answerOptionsAmount;

    public $questionTextPartials;
    public $QuestionTextPartialFinal;


    public function render()
    {
        return view('livewire.co-learning.completion-question');
    }

    public function updatedAnswerOptions()
    {
        $this->emit( 'UpdateAnswerRating', $this->answerOptionsChecked, $this->answerOptionsAmount);
    }

    public function isQuestionFullyAnswered(): bool
    {
        return collect($this->answer)->count() === $this->answerOptionsAmount;
    }

    protected function handleGetAnswerData()
    {
        $answer = Answer::find(494);
//        $this->answer = (array) json_decode($answer->json);
        $this->answer = (array) json_decode($this->answerRating->answer->json);

        $question_text = $this->cleanQuestionText($this->answerRating->answer->question->converted_question_html);
//        $question_text = $this->cleanQuestionText($answer->question->converted_question_html);

        $this->questionTextPartials = collect(explode('(##)',preg_replace(self::SEARCH_PATTERN, '(##)', $question_text)));
        $this->QuestionTextPartialFinal = $this->questionTextPartials->pop();

        $this->answerOptionsAmount = $this->questionTextPartials->count();

        for($i = 0; $i < $this->answerOptionsAmount; $i++){
            $this->answerOptions[] = [
                'rating' => null,
                'answered' => isset($this->answer[$i]),
                'answer' => $this->answer[$i] ?? '......',
            ];
        }

    }

    public function getAnswerOptionsCheckedProperty()
    {
        return collect($this->answerOptions)->reduce(function ($carry, $answerOption) {
            $carry += $answerOption['rating'] === '1' ? 1 : 0;
            return $carry;
        }, 0);
    }

    private function cleanQuestionText(string $questionText){
        return Str::replaceLast(
            '</p>',
            ' ',
            Str::replaceFirst(
                '<p>',
                '',
                $questionText
            )
        );
    }
}
