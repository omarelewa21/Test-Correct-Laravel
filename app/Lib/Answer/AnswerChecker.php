<?php namespace tcCore\Lib\Answer;

use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\TestParticipant;

class AnswerChecker {
    static protected $questions;

    public static function checkAnswerOfParticipant(TestParticipant $testParticipant) {
        $testTakeId = $testParticipant->testTake->getKey();

        $questions = QuestionGatherer::getQuestionsOfTest($testParticipant->testTake->getAttribute('test_id'), false);

        foreach ($testParticipant->answers as $answer) {
            $questionId = $answer->getAttribute('question_id');
            if (!array_key_exists($questionId, $questions)) {
                continue;
            }

            $question = $questions[$questionId];
            static::checkAnswerOfQuestion($testTakeId, $question, $answer);
        }
    }

    public static function checkAnswerOfQuestion($testTakeId, QuestionInterface $question, Answer $answer) {
        if (!$question instanceof QuestionInterface && !$answer instanceof Answer && $question->getKey() != $answer->getAttribute('question_id')) {
            return false;
        }

        if ($answer->getAttribute('json') === null) {
            $answerRating = new AnswerRating();
            $answerRating->setAttribute('type', 'SYSTEM');
            $answerRating->setAttribute('test_take_id', $testTakeId);
            $answerRating->setAttribute('rating', 0);

            return $answer->answerRatings()->save($answerRating);
        }

        if (!$question->canCheckAnswer()) {
            return false;
        }

        if (static::checkHasAnswerRating($answer)) {
            return true;
        }

        $rating = $question->checkAnswer($answer);
        if ($rating !== false) {
            $answerRating = new AnswerRating();
            $answerRating->setAttribute('type', 'SYSTEM');
            $answerRating->setAttribute('test_take_id', $testTakeId);
            $answerRating->setAttribute('rating', $rating);

            return $answer->answerRatings()->save($answerRating);
        } else {
            return false;
        }
    }

    protected static function checkHasAnswerRating(Answer $answer) {
        return ($answer->answerRatings()->where('user_id', null)->where('type', 'SYSTEM')->count() > 0);
    }
}