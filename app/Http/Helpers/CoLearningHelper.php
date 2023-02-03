<?php

namespace tcCore\Http\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\TestParticipant;
use tcCore\TestTake;

class CoLearningHelper extends BaseHelper
{
    static $test_take_id;

    public $testTakeId;
    public $discussingQuestionId;

    public static function getTestParticipantsWithStatus(string $testTakeId, int $discussingQuestionId)
    {
        //gate if not teacher or invigilator?

//        $ownTestParticipant = 1486;

        //return collection testparticipants with abnormalities and activity status
        $instance = new static;
        $instance->testTakeId = $testTakeId;
        $instance->discussingQuestionId = $discussingQuestionId;

        return $instance->getTestParticipants();
    }


    public static function getTestParticipantsWithStatusOldController(TestTake $testTake, Request $request)
    {
        return (new TestTakesController)->showFromWithin($testTake, $request, false);

        /**
         *needs to query
         * testParticipants
         *also queries:
         * Test
         * test.subject
         * invigilatorUsers
         * testParticipants.testTakeSTatus //dont need full model?
         * testParticipants.user //dont need full model?
         * testParticipants.user.schoolclass //dont need full model?
         * discussingQuestion
         * testTakeStatus
         *
         */
    }

    protected function getTestParticipants() {

        /**
         * needed properties:
         *testParticipants
         * ->active
         * ->answer_rated
         * ->answer_to_rate
         * ->abnormalities
         */

        $this->testTakeId;
        $this->discussingQuestionId;


        return static::testParticipantsQuery($this->testTakeId, $this->discussingQuestionId)->get();

    }

    public static function fullTestParticipantsQuery($testTakeId, $discussingQuestionId)
    {
        $testParticipants = CoLearningHelper::testParticipantsQuery($testTakeId, $discussingQuestionId);
        $abnormalitiesSubQuery = CoLearningHelper::getAbnormalitiesQuery();

        $testParticipants
            ->joinSub($abnormalitiesSubQuery, 'abnormalities', 'abnormalities.user_id', '=', 'test_participants.user_id')
            ->addSelect('abnormalities.abnormalities');

        return $testParticipants;
    }

    public static function testParticipantsQuery($testTakeId, $discussingQuestionId)
    {
        $date = now()->subSeconds(30)->format('Y-m-d H:i:s');


        return TestParticipant::where('test_participants.test_take_id', $testTakeId)// $testTakeId)
        //add ->active (bool)
        //answer_rated (string|int) CONVERT(..., SIGNED) casts the string(NEWDECIMAL) to an int
        //answer_to_rate (string|int) if json !== null (isAnswered) then you need to rate it.
        ->selectRaw(
            sprintf(
                'test_participants.*, 
                    CASE WHEN heartbeat_at >= "%s" THEN 1 ELSE 0 END as active,
                    CONVERT(SUM(if(answers.json IS NOT NULL,1,0)), SIGNED) as answer_to_rate,
                    CONVERT(SUM(answer_ratings.rating IS NOT null), SIGNED) as answer_rated',
                $date
            )
        )->join('answer_ratings', 'answer_ratings.user_id', '=', 'test_participants.user_id')
            ->join('answers', 'answer_ratings.answer_id', '=', 'answers.id')
            ->where('answers.question_id', '=',$discussingQuestionId)
            ->where('test_participants.test_take_id', '=', $testTakeId)
            ->where('answer_ratings.test_take_id', '=', $testTakeId)
            ->where('answer_ratings.type', '=', 'STUDENT')

            ->groupBy('test_participants.id');
    }


    public static function getAbnormalitiesQuery()
    {
        $testTakeId = 19;
        static::$test_take_id = $testTakeId;


        $student_ratings_sub = DB::query()
            ->select(['answer_ratings.answer_id', 'answer_ratings.rating', 'answer_ratings.user_id'])
            ->from('answers')->crossJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
            ->where('answer_ratings.test_take_id', '=', $testTakeId)
            ->where('answer_ratings.type', '=', 'STUDENT');
        $teacher_ratings_sub = DB::query()
            ->select(['answer_ratings.answer_id', 'answer_ratings.rating as teacher_rating'])
            ->from('answers')->leftJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
            ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.type', '=', 'TEACHER');
        $system_ratings_sub = DB::query()
            ->select(['answer_ratings.answer_id', 'answer_ratings.rating as system_rating'])
            ->from('answers')->leftJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
            ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.type', '=', 'SYSTEM');


        $student_abnormalities_sub = DB::query()->select(['student_abnormalities.user_id','student_abnormalities.answer_id','abnormalities'])
            ->fromSub(function ($query) {
                $query->select(['answer_ratings.answer_id', 'answer_ratings.user_id'])
                    ->from('answers')->crossJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
                    ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
                    ->where('answer_ratings.type', '=', 'STUDENT');
            }, 'student_abnormalities')
            ->JoinSub(function ($query) {
                $query->selectRaw('answer_ratings.answer_id,
                                  CASE
                                      WHEN COUNT(DISTINCT
                                                 CASE WHEN answer_ratings.`rating` IS NOT NULL THEN rating END) > 1
                                          THEN 1
                                      ELSE 0 END AS abnormalities')
                    ->from('answers')->crossJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
                    ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
                    ->where('answer_ratings.type', '=', 'STUDENT')
                ->groupBy('answer_ratings.answer_id');
            }, 'student_answer_abnormalities', 'student_abnormalities.answer_id','=', 'student_answer_abnormalities.answer_id', 'cross');


        //total combined
        $abnormalities =  DB::query()->selectRaw('total.user_id, sum(total.abnormalities) as abnormalities')
            ->fromSub(function ($query) use ($student_ratings_sub, $teacher_ratings_sub, $system_ratings_sub, $student_abnormalities_sub) {
                $query->selectRaw('student_ratings.answer_id, student_ratings.user_id, CASE
                    WHEN teacher_rating IS NOT NULL THEN if(student_ratings.rating != teacher_rating, 1, 0)
                    WHEN system_rating IS NOT NULL THEN if(student_ratings.rating != system_rating, 1, 0)
                    WHEN student_abnormalities.abnormalities > 0 THEN 1
                    ELSE 0 END as abnormalities
                ')
                    ->fromSub($student_ratings_sub, 'student_ratings')
                    ->joinSub($teacher_ratings_sub, 'teacher_ratings', 'teacher_ratings.answer_id', '=', 'student_ratings.answer_id', 'left')
                    ->joinSub($system_ratings_sub, 'system_ratings', 'system_ratings.answer_id', '=', 'student_ratings.answer_id', 'left')
                    ->joinSub($student_abnormalities_sub, 'student_abnormalities', function ($join) {
                        $join->on('student_abnormalities.user_id', '=', 'student_ratings.user_id');
                        $join->on('student_abnormalities.answer_id', '=', 'student_ratings.answer_id');
                    }, type: 'left');

            }, 'total')
            ->groupBy('total.user_id');

        return $abnormalities;


        return $student_ratings_sub->get();
        return $teacher_ratings_sub->get();
        return $system_ratings_sub->get();
        return $student_abnormalities_sub->get();
    }
}