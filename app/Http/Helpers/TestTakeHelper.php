<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Collection;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

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
            ->where('questions.type', '!=', 'InfoscreenQuestion')
            ->with(['answer'])
            ->get();

        $questionTally = [];

        $ratedQuestionIds = $ratings->where('type', '!=', AnswerRating::TYPE_STUDENT)
            ->each(function ($rating) use (&$questionTally) {
                $questionId = $rating->question_id;
                $questionTally[$questionId] = ($questionTally[$questionId] ?? 0) + 1;
            })
            ->pluck('question_id')
            ->unique();

        $ratings
            ->where('type', '=', AnswerRating::TYPE_STUDENT)
            ->whereNotIn('question_id', $ratedQuestionIds)
            ->groupBy('answer_id')
            ->each(function (Collection $answerGroup) use (&$questionTally) {
                if ($answerGroup->first()?->answer->hasCoLearningDiscrepancy() === false) {
                    $questionId = $answerGroup->first()?->answer->question_id;
                    $questionTally[$questionId] = ($questionTally[$questionId] ?? 0) + 1;
                };
            });

        Answer::selectRaw('question_id, COUNT(test_participant_id) as required_ratings')
            ->join('test_participants', 'test_participants.id', '=', 'answers.test_participant_id')
            ->where('test_participants.test_take_id', $testTake->getKey())
            ->where('test_participants.test_take_status_id', '>', TestTakeStatus::STATUS_TEST_NOT_TAKEN)
            ->groupBy('question_id')
            ->get()
            ->mapWithKeys(fn($answer) => [$answer->question_id => $answer->required_ratings])
            ->each(function ($required, $questionId) use (&$questionTally) {
                $questionTally[$questionId] = ['required' => $required, 'count' => $questionTally[$questionId]];
            });

        return collect($questionTally)->where(fn($arr) => $arr['required'] === $arr['count'])->count();
    }

    private static function ratingsQuery(TestTake $testTake)
    {
        return AnswerRating::leftJoin('answers as a', 'answer_ratings.answer_id', '=', 'a.id')
            ->leftJoin('questions', 'questions.id', '=', 'a.question_id')
            ->where('answer_ratings.test_take_id', $testTake->getKey())
            ->whereNotNull('answer_ratings.rating')
            ->whereNull('a.deleted_at');
    }
}