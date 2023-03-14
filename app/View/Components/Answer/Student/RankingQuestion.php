<?php

namespace tcCore\View\Components\Answer\Student;

use tcCore\Answer;
use tcCore\Question;

class RankingQuestion extends QuestionComponent
{
    public $answerStruct;

    public function __construct(Question $question, Answer $answer)
    {
        parent::__construct($question, $answer);
        $this->setAnswerStruct($question, $answer);
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $struct = collect(json_decode($answer->json));
        $correctAnswers = $question->getCorrectAnswerStructure();

        $this->answerStruct = $struct->map(function ($order, $questionId) use ($correctAnswers) {
            $link = $correctAnswers->first(fn($link) => $link->ranking_question_answer_id === $questionId);
            $link->answeredOrder = $order;
            return $link;
        })->sortBy('answeredOrder');
    }
}