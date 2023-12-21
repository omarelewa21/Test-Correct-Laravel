<?php namespace tcCore\Lib\Question;

interface QuestionInterface {

    public function loadRelated();

    public function canCreateSystemRatingForAnswer($answer): bool;

    public function checkAnswer($answer);

    public function isClosedQuestion(): bool;
}