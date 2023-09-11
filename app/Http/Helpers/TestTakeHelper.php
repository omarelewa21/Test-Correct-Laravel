<?php

namespace tcCore\Http\Helpers;

use Illuminate\Database\Eloquent\Builder;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\TestParticipant;
use tcCore\TestTake;

class TestTakeHelper
{
    /**
     * @param TestTake $testTake
     * @param $type 'assess' | 'discuss'
     * @return Builder
     */
    public static function nonAssessedDiscussedQuestionIdQueryBuilder(TestTake $testTake, $type = 'assess') : Builder
    {
        $answerRatingQueryBuilder = AnswerRating::where('test_take_id', $testTake->getKey())
            ->when($type === 'assess', function ($query) {
                $query->where('type', '!=', AnswerRating::TYPE_STUDENT);
            }, function ($query) {
                $query->where('type', AnswerRating::TYPE_STUDENT);
            })
            ->whereNotNull('rating')
            ->select('answer_id');

        $testParticipantQueryBuilder = TestParticipant::where('test_take_id', $testTake->getKey())->where('test_take_status_id', '>=', 6)->select('id');
        return Answer::whereIn('test_participant_id', $testParticipantQueryBuilder)->whereNotIn('id', $answerRatingQueryBuilder)->groupBy('question_id')->select('question_id');
    }

}