<?php namespace tcCore\Lib\Question;

interface QuestionInterface {

    public function loadRelated();

    public function canCheckAnswer($answer);

    public function checkAnswer($answer);

    public function isClosedQuestion(): bool;
}