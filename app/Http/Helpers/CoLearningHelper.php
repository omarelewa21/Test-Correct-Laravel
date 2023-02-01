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

    public static function getTestParticipantsWithStatus(string $testTakeId, string $teacherUserId)
    {
        //gate if not teacher or invigilator?

//        $ownTestParticipant = 1486;

        //return collection testparticipants with abnormalities and activity status
        $instance = new static;
        $instance->testTakeId = $testTakeId;
        $instance->teacherUserId = $teacherUserId;

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

        $date = "2020-01-01 18:01:01";
        $date = now()->subSeconds(30)->format('Y-m-d H:i:s');

        return TestParticipant::where('test_take_id', $this->testTakeId)
            //add ->active (bool)
            ->selectRaw(
                sprintf('*, CASE WHEN heartbeat_at >= DATE("%s") THEN 1 ELSE 0 END as active', $date)
            )
            //abnormalities?

            //answer_to_rate
            //answer_rated
            ->groupBy('test_participants.id')
            ->get();




        //tp->active = ( hearbeat_at >= now()->subSeconds(30) );


        //select
        //CASE WHEN heartbeat_at >= DATE("2020-01-01") THEN 1
        //     ELSE 0
        //END as active,
        //count(ar.id) as answer_to_rate,
        //SUM(ar.rating IS NOT null) as answer_rated,
        //test_participants.*
        //
        //from test_participants
        //join answer_ratings as ar on ar.user_id = test_participants.user_id
        //
        //where ar.test_take_id = 19
        //	AND test_participants.test_take_id = 19
        //	AND ar.type = "STUDENT"
        //	AND ar.answer_id IN (select id from answers where question_id = 241 AND test_participant_id IN (select id from test_participants where test_take_id = 19))
        //
        //group By test_participants.id


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

        //-- one (answers in subQuery)
        //explain select
        //CASE WHEN heartbeat_at >= "2023-02-01 16:01:01" THEN 1
        //     ELSE 0
        //END as active,
        //count(ar.id) as answer_to_rate,
        //SUM(ar.rating IS NOT null) as answer_rated,
        //test_participants.*
        //
        //from test_participants
        //join answer_ratings as ar on ar.user_id = test_participants.user_id
        //
        //where ar.test_take_id = 19
        //	AND test_participants.test_take_id = 19
        //	AND ar.type = "STUDENT"
        //	AND ar.answer_id IN (select id from answers where question_id = 241 AND test_participant_id IN (select id from test_participants where test_take_id = 19))
        //
        //group By test_participants.id
        //
        //
        //
        //-- two
        //explain select
        //CASE WHEN heartbeat_at >= DATE("2020-01-01 16:00:00") THEN 1
        //     ELSE 0
        //END as active,
        //count(ar.id) as answer_to_rate,
        //SUM(ar.rating IS NOT null) as answer_rated,
        //test_participants.*
        //
        //from test_participants
        //join answer_ratings as ar on ar.user_id = test_participants.user_id
        //join answers as a on ar.answer_id = a.id AND a.question_id = 241
        //
        //where ar.test_take_id = 19
        //	AND test_participants.test_take_id = 19
        //	AND ar.type = "STUDENT"
        //
        //group By test_participants.id
    }
}