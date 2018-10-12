<?php namespace tcCore\Lib\Question;

interface QuestionInterface {

    public function loadRelated();

    public function canCheckAnswer();

    public function checkAnswer($answer);
}