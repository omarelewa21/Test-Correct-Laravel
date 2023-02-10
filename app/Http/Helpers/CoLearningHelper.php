<?php

namespace tcCore\Http\Helpers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\TestParticipant;
use tcCore\TestTake;

class CoLearningHelper extends BaseHelper
{
    static $test_take_id;
    static $discussing_question_id;

    public static function getTestParticipantsWithStatusAndAbnormalities($testTakeId, $discussingQuestionId)
    {
        return CoLearningHelper::buildTestParticipantsQuery($testTakeId, $discussingQuestionId)->get();
    }

    public static function nextQuestion(TestTake $testTake)
    {
        return (new TestTakesController)
            ->nextQuestion($testTake);
    }


    private static function buildTestParticipantsQuery($testTakeId, $discussingQuestionId)
    {
        static::$test_take_id = $testTakeId;
        static::$discussing_question_id = $discussingQuestionId;
        $heartbeatStillActiveThresholdDatetime = now()->subSeconds(30)->format('Y-m-d H:i:s');

        return TestParticipant::where('test_participants.test_take_id', static::$test_take_id)
            ->selectRaw(
                sprintf(
                    'test_participants.*, 
                    CASE WHEN heartbeat_at >= "%s" THEN 1 ELSE 0 END as active,
                    CONVERT(SUM(answers.done = 1), SIGNED) as answer_to_rate,
                    CONVERT(SUM(answer_ratings.rating IS NOT null), SIGNED) as answer_rated',
                    $heartbeatStillActiveThresholdDatetime
                )
            )->addSelect('abnormalities.abnormalities')
            ->join('answer_ratings', 'answer_ratings.user_id', '=', 'test_participants.user_id')
            ->join('answers', 'answer_ratings.answer_id', '=', 'answers.id')
            ->joinSub(
                query: CoLearningHelper::getAbnormalitiesQuery(),
                as: 'abnormalities',
                first: 'abnormalities.user_id',
                operator: '=',
                second: 'test_participants.user_id'
            )->where('answers.question_id', '=', static::$discussing_question_id)
            ->where('test_participants.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.type', '=', AnswerRating::TYPE_STUDENT)
            ->groupBy('test_participants.id');
    }


    private static function getAbnormalitiesQuery() : Builder
    {
        return DB::query()->selectRaw('total.user_id, sum(total.abnormalities) as abnormalities')
            ->fromSub(function ($query) {
                $query->selectRaw('student_ratings.answer_id, student_ratings.user_id, CASE
                    WHEN teacher_rating IS NOT NULL THEN if(student_rating != teacher_rating, 1, 0)
                    WHEN system_rating IS NOT NULL THEN if(student_rating != system_rating, 1, 0)
                    WHEN student_abnormalities.abnormalities > 0 THEN 1
                    ELSE 0 END as abnormalities
                ')
                    ->fromSub(static::getRatingsSubQueryPerType(AnswerRating::TYPE_STUDENT), 'student_ratings')
                    ->joinSub(static::getRatingsSubQueryPerType(AnswerRating::TYPE_TEACHER), 'teacher_ratings', 'teacher_ratings.answer_id', '=', 'student_ratings.answer_id', 'left')
                    ->joinSub(static::getRatingsSubQueryPerType(AnswerRating::TYPE_SYSTEM), 'system_ratings', 'system_ratings.answer_id', '=', 'student_ratings.answer_id', 'left')
                    ->joinSub(static::getStudentVsStudentAbnormalitiesSubQuery(), 'student_abnormalities', function ($join) {
                        $join->on('student_abnormalities.user_id', '=', 'student_ratings.user_id');
                        $join->on('student_abnormalities.answer_id', '=', 'student_ratings.answer_id');
                    }, type: 'left');
            }, 'total')
            ->groupBy('total.user_id');
    }

    private static function getStudentVsStudentAbnormalitiesSubQuery() : Builder
    {
        return DB::query()->select(['student_abnormalities.user_id', 'student_abnormalities.answer_id', 'abnormalities'])
            ->fromSub(static::getRatingsSubQueryPerType(AnswerRating::TYPE_STUDENT, true), 'student_abnormalities')
            ->JoinSub(function ($query) {
                $query->selectRaw('answer_ratings.answer_id,
                                  CASE
                                      WHEN COUNT(
                                              DISTINCT CASE WHEN answer_ratings.`rating` IS NOT NULL THEN rating END
                                           ) > 1 THEN 1
                                      ELSE 0 
                                      END AS abnormalities')
                    ->from('answers')->crossJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
                    ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
                    ->where('answer_ratings.type', '=', AnswerRating::TYPE_STUDENT)
                    ->where('answer_ratings.deleted_at', '=', null)
                    ->groupBy('answer_ratings.answer_id');
            }, 'student_answer_abnormalities', 'student_abnormalities.answer_id', '=', 'student_answer_abnormalities.answer_id', 'cross');
    }

    private static function getRatingsSubQueryPerType($type, $excludeRating = false) : Builder
    {
        if (!is_string($type) || !in_array($type = Str::upper($type), [AnswerRating::TYPE_STUDENT, AnswerRating::TYPE_TEACHER, AnswerRating::TYPE_SYSTEM])) {
            throw new \Exception(sprintf("Rating type %s doesn't exist", $type));
        }

        $selectColumnNames = collect([
            'answer_ratings.answer_id',
        ])->when(
            $type === AnswerRating::TYPE_STUDENT,
            fn($collection) => $collection->push('answer_ratings.user_id')
        )->when(
            $excludeRating === false,
            fn($collection) => $collection->push(sprintf('answer_ratings.rating as %s_rating', Str::lower($type)))
        )->toArray();


        return DB::query()
            ->select($selectColumnNames)
            ->from('answers')
            ->crossJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
            ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.type', '=', $type)
            ->where('answer_ratings.deleted_at', '=', null);
    }
}