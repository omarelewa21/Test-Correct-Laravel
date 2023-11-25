<?php namespace tcCore\Lib\Answer;

use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\TestParticipant;
use tcCore\Traits\CanClearStaticProperties;

class AnswerChecker {

    use CanClearStaticProperties;

    static protected $questions;

    public static function checkAnswerOfParticipant(TestParticipant $testParticipant, bool $recalculate = false, bool $dryRun = false, $commandEnv = null) {
        $testTakeId = $testParticipant->testTake->getKey();

        $questions = QuestionGatherer::getQuestionsOfTest($testParticipant->testTake->getAttribute('test_id'), false);

        $changed = false;

        foreach ($testParticipant->answers as $answer) {
            $questionId = $answer->getAttribute('question_id');
            if (!array_key_exists($questionId, $questions)) {
                continue;
            }

            $question = $questions[$questionId];
            if(static::checkAnswerOfQuestion($testTakeId, $question, $answer, $recalculate, $dryRun, $commandEnv)){
                $changed = true;
            }
        }

        return $changed;
    }

    protected static function getAnswerRating($testTakeId, Answer $answer, bool $recalculate = false)
    {
        if($recalculate === false){
            return new AnswerRating();
        }

        $answerRating = AnswerRating::where('answer_id',$answer->getKey())->where('test_take_id',$testTakeId)->where('type','SYSTEM')->first();
        if(null == $answerRating){
            return new AnswerRating();
        }

        return $answerRating;

    }

    protected static function checkAnswerOfQuestion($testTakeId, QuestionInterface $question, Answer $answer, bool $recalculate = false, bool $dryRun = false, $commandEnv = null) {
        if (!$question instanceof QuestionInterface && !$answer instanceof Answer && $question->getKey() != $answer->getAttribute('question_id')) {
            return false;
        }

        $changed = false;

        if ($answer->getAttribute('json') === null) {
            $answerRating = self::getAnswerRating($testTakeId, $answer, $recalculate);
            $answerRating->setAttribute('type', 'SYSTEM');
            $answerRating->setAttribute('test_take_id', $testTakeId);
            $answerRating->setAttribute('rating', 0);

            if($answerRating->isDirty()){
                $changed = true;
            }

            if($dryRun === false) {
                $answer->answerRatings()->save($answerRating);
            }
            return $changed;
        }

        if (!$question->canCheckAnswer()) {
            return false;
        }

        if (static::checkHasAnswerRating($answer, $recalculate)) {
            return false;
        }

        $rating = $question->checkAnswer($answer);
        if ($rating !== false) {
            $answerRating = self::getAnswerRating($testTakeId, $answer, $recalculate);
            if($recalculate && null !== $commandEnv){
                $text = sprintf('ANSWERID: %d; van %s => %s',$answer->getKey(), $answerRating->rating, $rating);
                if($answerRating->rating > $rating){
                    $changed = true;
                    $commandEnv->toError($text);
                } else if ((int) $answerRating->rating == (int) $rating) {
                    $changed = false;
                    $commandEnv->toComment($text);
                } else {
                    $changed = true;
                    $commandEnv->toInfo($text);
                }
            }
            $answerRating->setAttribute('type', 'SYSTEM');
            $answerRating->setAttribute('test_take_id', $testTakeId);
            $answerRating->setAttribute('rating', $rating);

//            if($answerRating->isDirty()){
//                $changed = true;
//            }

            if($dryRun === false) {
                $answer->answerRatings()->save($answerRating);
            }

            return $changed;
        } else {
            return false;
        }
    }

    protected static function checkHasAnswerRating(Answer $answer, bool $recalculate = false) {
        if($recalculate === false) {
            return ($answer->answerRatings()->where('user_id', null)->where('type', 'SYSTEM')->count() > 0);
        }
        return false;
    }
}