<?php namespace tcCore\Lib\Repositories;

use Illuminate\Support\Facades\Log;
use tcCore\Answer;
use tcCore\TestParticipant;

class AnswerRepository {
    public static function getTestParticipantScores(TestParticipant $testParticipant) {
        $scores = new \Stdclass();
        $scores->current = 0;
        $scores->max = 0;

        $answers = Answer::where('test_participant_id', $testParticipant->getKey())->with(['answerRatings', 'question' => function($query) {
            $query->withTrashed();
        }])->get(['id', 'final_rating', 'ignore_for_rating', 'question_id']);
        foreach($answers as $answer) {
            if ($answer->getAttribute('ignore_for_rating') == 1) {
                continue;
            }

            $answerScore = $answer->getAttribute('final_rating');

            if ($answerScore === null) {
                $answerScore = $answer->calculateFinalRating();
                if ($answerScore !== null) {
                    $answer->setAttribute('final_rating', $answerScore);
                    $answer->save();
                }
            }
            $scores->current += $answerScore;

            $scores->max += $answer->question->getAttribute('score');
        }

        return $scores;
    }
}