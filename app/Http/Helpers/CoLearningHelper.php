<?php

namespace tcCore\Http\Helpers;

use Illuminate\Http\Request;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\TestParticipant;
use tcCore\TestTake;

class CoLearningHelper extends BaseHelper
{
    public $testTakeId;
    public $teacherUserId;
    public $discussingQuestionId;

    public static function getTestParticipantsWithStatus(string $testTakeId, string $teacherUserId, int $discussingQuestionId)
    {
        //gate if not teacher or invigilator?

//        $ownTestParticipant = 1486;

        //return collection testparticipants with abnormalities and activity status
        $instance = new static;
        $instance->testTakeId = $testTakeId;
        $instance->teacherUserId = $teacherUserId;
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
         *
         */

        $this->testTakeId;
        $this->teacherUserId;
        $this->discussingQuestionId;

//        $date = "2020-01-01 18:01:01";
        $date = now()->subSeconds(30)->format('Y-m-d H:i:s');

        return TestParticipant::where('test_participants.test_take_id', $this->testTakeId)// $this->testTakeId)
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
            ->where('answers.question_id', '=',$this->discussingQuestionId)//241)
            ->where('test_participants.test_take_id', '=', $this->testTakeId)
            ->where('answer_ratings.test_take_id', '=', $this->testTakeId)
            ->where('answer_ratings.type', '=', 'STUDENT')
            //abnormalities?

            ->groupBy('test_participants.id')
            ->get();


        //select
        //CASE WHEN heartbeat_at >= DATE("2020-01-01 16:00:00") THEN 1
        //     ELSE 0
        //END as active,
        //a.json,
        //test_participants.*
        //
        //from test_participants
        //join answer_ratings as ar on ar.user_id = test_participants.user_id
        //join answers as a on ar.answer_id = a.id
        //
        //where ar.test_take_id = 22
        //	AND test_participants.test_take_id = 22
        //	AND ar.type = "STUDENT"
        //	AND a.question_id = 250


        /*todo
         * abnormalities:
         *  teacher or system rating:
         *      ANSWER_RATING student
         *      Answer_rating rating not null
         *      Answer_rating rating not 'wantedrating' (system or teacher)
         *        THEN=> abnormalities[tpId] +1 (!isset = 0, then ++)
         *  only student ratings:
         *
         *  no ratings:
         */



        //--  backup queries

        //explain
        //select
        //CASE WHEN test_participants.heartbeat_at >= DATE("2020-01-01 16:00:00") THEN 1
        //     ELSE 0
        //END as active,
        //answers.json,
        //test_participants.*
        //
        //from test_participants
        //join answer_ratings on answer_ratings.user_id = test_participants.user_id
        //join answers on answer_ratings.answer_id = answers.id
        //
        //where answer_ratings.test_take_id = 19
        //	AND test_participants.test_take_id = 19
        //	AND answer_ratings.type = "STUDENT"
        //	AND answers.question_id = 241
    }
}