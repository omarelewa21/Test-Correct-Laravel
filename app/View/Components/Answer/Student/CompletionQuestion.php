<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Str;
use tcCore\Http\Traits\Questions\WithCompletionConversion;

class CompletionQuestion extends QuestionComponent
{
    use WithCompletionConversion;

    public mixed $questionTextPartials = [];
    public mixed $questionTextPartialFinal = [];
    public $answerStruct;

    protected function setAnswerStruct($question, $answer): void
    {
        $correctAnswers = $question->getCorrectAnswerStructure();
        $answers = json_decode($answer->json ?? '{}', true);

        $this->answerStruct = $question->isSubType('completion')
            ? $this->createCompletionAnswerStruct($answers, $correctAnswers, $answer)
            : $this->createSelectionAnswerStruct($answers, $correctAnswers);

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question->converted_question_html);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();
    }


    /**
     * @param $givenAnswer
     * @param $correctAnswer
     * @return bool|null
     */
    private function isToggleActiveForAnswer($givenAnswer, $correctAnswer): ?bool
    {
        if (!$this->answer->answerRatings) return null;
        if ($this->question->isSubType('completion') && !$this->question->auto_check_answer) return null;

        if ($this->question->auto_check_answer_case_sensitive) {
            return $givenAnswer === $correctAnswer;
        }

        return Str::lower($givenAnswer) === Str::lower($correctAnswer);
    }

    private function createCompletionAnswerStruct(mixed $answers, $correctAnswers, $answer)
    {
        return $correctAnswers->map(function ($link, $key) use ($answer, $answers, $correctAnswers) {
            $score = $this->question->score / $correctAnswers->where('correct', 1)->count();
            return $this->setAnswerPropertiesOnObject($link, $key, $link, $answers, $score);
        });
    }

    private function createSelectionAnswerStruct(mixed $answers, $correctAnswers)
    {
        if ($answers) {
            return collect($answers)->map(function ($link, $key) use ($answers, $correctAnswers) {
                $answer = (object)$link;
                $correctAnswer = $correctAnswers->where('correct', 1)->values()->get($key - 1);
                $score = $this->question->score / $correctAnswers->where('correct', 1)->count();
                return $this->setAnswerPropertiesOnObject($answer, $key, $correctAnswer, $answers, $score);
            })->values();
        }

        return $correctAnswers->where('correct', 1)->map(function ($link) {
            return $this->setAnswerPropertiesOnObject($link, 0, [], [], 0);
        })->values();
    }

    private function setAnswerPropertiesOnObject($object, $key, $correctAnswer, $answers, $score)
    {
        $hasValue = isset($answers[$key]) && filled($answers[$key]);
        $object->answerText = $hasValue ? $answers[$key] : '......';
        $object->answered = $hasValue;
        $object->activeToggle = $hasValue ? $this->isToggleActiveForAnswer($answers[$key], $correctAnswer->answer) : null;
        $object->score = $score;
        return $object;
    }
}