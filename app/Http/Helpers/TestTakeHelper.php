<?php

namespace tcCore\Http\Helpers;

use Illuminate\Database\Eloquent\Builder;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\UserFeatureSetting;

class TestTakeHelper
{
    /**
     * @param TestTake $testTake
     * @param $type 'assess' | 'discuss'
     * @return Builder
     */
    public static function nonAssessedDiscussedQuestionIdQueryBuilder(
        TestTake $testTake,
                 $type = 'assess',
                 $takeNonDiscrepancyIntoAccount = false
    ): Builder {
        $answerRatingQueryBuilder = self::ratingsQuery($testTake)
            ->whereNot('answer_ratings.type', AnswerRating::TYPE_STUDENT)
            ->select('answer_id');

//        $testParticipantQueryBuilder = TestParticipant::where('test_take_id', $testTake->getKey())->where('test_take_status_id', '>=', 6)->select('id');
        $testParticipantQueryBuilder = $testTake->testParticipants()
            ->where('test_take_status_id', '>', TestTakeStatus::STATUS_DISCUSSING)
            ->select('id');

        $qIdsBuilder = Answer::whereIn('test_participant_id', $testParticipantQueryBuilder)
            ->leftJoin('questions', 'answers.question_id', '=', 'questions.id')
            ->when(
                $testTake->test_take_status_id >= TestTakeStatus::STATUS_DISCUSSING,
                function ($query) use ($answerRatingQueryBuilder) {
                    $query->whereNotIn('answers.id', $answerRatingQueryBuilder)
                        ->where(
                            'questions.type',
                            '!=',
                            'infoScreen'
                        ); // for students and teachers there is no infoscreen to rate
                }
            )
            ->groupBy('question_id')
            ->select('question_id');

        if ($type === 'assess' && $qIdsBuilder->get()->count() !== 0) {
            // we could have non discrepency questions which we don't necessarily have to show
            if ($takeNonDiscrepancyIntoAccount && $testTake->test_take_status_id >= 8 && !$testTake->skipped_discussion) {
                // it is allowed to skip questions without discrepancies
                $answers = Answer::whereIn('test_participant_id', $testParticipantQueryBuilder)
                    ->whereNotIn('id', $answerRatingQueryBuilder)
                    ->get();
                $nonDiscrepancyAnswerIds = $answers->filter(function (Answer $answer) {
                    return !$answer->hasCoLearningDiscrepancy();
                })->map(function (Answer $answer) {
                    return $answer->id;
                });
                return $qIdsBuilder->whereNotIn('answers.id', $nonDiscrepancyAnswerIds);
            }
        }

        return $qIdsBuilder;
    }

    private function getSkipDiscrepancyValue(TestTake $testTake)
    {
        if ($testTake->skipped_discussion) {
            return false;
        }

        if ($this->canUseDiscrepancyToggle()) {
            return $this->getSessionSettingValue('assessment_skip_no_discrepancy_answer');
        }
        return false;
    }

    public static function getDiscussedQuestionCount(TestTake $testTake): int
    {
        return self::ratingsQuery($testTake)
            ->where('answer_ratings.type', AnswerRating::TYPE_STUDENT)
            ->selectRaw("COUNT(DISTINCT a.question_id) as question_count")
            ->value('question_count');
    }

    public static function getAssessedQuestionCount(TestTake $testTake, bool $skipNoDiscrepancies = false): int
    {
        return self::nonAssessedDiscussedQuestionIdQueryBuilder(
            testTake: $testTake,
            takeNonDiscrepancyIntoAccount: !$skipNoDiscrepancies
        )
            ->count();
    }

    protected static function ratingsQuery(TestTake $testTake)
    {
        return AnswerRating::leftJoin('answers as a', 'answer_ratings.answer_id', '=', 'a.id')
            ->leftJoin('questions', 'questions.id', '=', 'a.question_id')
            ->where('answer_ratings.test_take_id', $testTake->getKey())
            ->whereNotNull('answer_ratings.rating')
            ->whereNull('a.deleted_at')
            ->where('questions.type', '!=', 'InfoscreenQuestion');
    }
}