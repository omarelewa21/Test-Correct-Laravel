<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Collection;
use tcCore\AnswerRating;
use tcCore\TestTake;

class TestTakeHelper
{
    public static function getDiscussedQuestionCount(TestTake $testTake): int
    {
        return self::ratingsQuery($testTake)
            ->selectRaw("COUNT(DISTINCT a.question_id) as question_count")
            ->where('answer_ratings.type', AnswerRating::TYPE_STUDENT)
            ->value('question_count');
    }

    public static function getAssessedQuestionCount(TestTake $testTake): int
    {
        $ratings = self::ratingsQuery($testTake)
            ->select(['answer_ratings.*', 'a.question_id'])
            ->with(['answer'])
            ->get();

        $ratedQuestionIds = $ratings->where('type', '!=', AnswerRating::TYPE_STUDENT)
            ->pluck('question_id')
            ->unique();

        $noDiscrepancyQuestionIds = $ratings
            ->where('type', '=', AnswerRating::TYPE_STUDENT)
            ->whereNotIn('question_id', $ratedQuestionIds)
            ->groupBy('answer_id')
            ->filter(
                fn(Collection $answerGroup) => $answerGroup->first()?->answer->hasCoLearningDiscrepancy() === false
            )
            ->flatten()
            ->pluck('question_id')
            ->unique();
        return $ratedQuestionIds->merge($noDiscrepancyQuestionIds)->count();
    }

    private static function ratingsQuery(TestTake $testTake)
    {
        return AnswerRating::leftJoin('answers as a', 'answer_ratings.answer_id', '=', 'a.id')
            ->leftJoin('questions', 'questions.id', '=', 'a.question_id')
            ->where('answer_ratings.test_take_id', $testTake->getKey())
            ->whereNotNull('answer_ratings.rating')
            ->whereNull('a.deleted_at')
            ->where('questions.type', '!=', 'InfoscreenQuestion');
    }
}