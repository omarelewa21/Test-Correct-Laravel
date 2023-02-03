<?php

namespace Tests\Unit;

use Composer\DependencyResolver\Request;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use tcCore\Http\Helpers\CoLearningHelper;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\SchoolLocation;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;

class CoLearningHelperTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function canGetTestParticipantsDataWithNewQuery()
    {
        $user = User::find(1486);
        auth()->login($user);

        $testTakeId = 19;
//        $testTakeId = 22    ;
        $testTakeUuid = '779168f6-4afe-4153-9102-9a131134ffa7';

//        $testTake = TestTake::whereUuid($testTakeUuid)->first();
        $testTake = TestTake::find($testTakeId);

        $request = new \Illuminate\Http\Request([
            'with' => ['participantStatus', 'discussingQuestion'],
        ]);
        $benchmark = [];
        $result1 = null;
        $result2 = null;

        //end of set-up


        DB::enableQueryLog();

        $benchmark[1]['time'] =  Benchmark::measure(function () use (&$result1, $testTake, $request) {
            return $result1 = CoLearningHelper::getTestParticipantsWithStatusOldController(
                testTake: $testTake,
                request: $request,
            );
        });

        $this->handleQueryLog($benchmark, 1);

        $benchmark[2]['time'] = Benchmark::measure(function () use (&$result2, $testTakeId, $user, $testTake) {
            return $result2 = CoLearningHelper::fullTestParticipantsQuery($testTakeId, $testTake->discussing_question_id)->get();
//            return $result2 = CoLearningHelper::getTestParticipantsWithStatus($testTakeId, $testTake->discussing_question_id);
        });

        $this->handleQueryLog($benchmark, 2);

        $result3 = $result2->sortBy('id')->map(function ($tp) {
            $temp = [];
            $temp['active'] = (bool) $tp->active;
            $temp['answer_to_rate'] = $tp->answer_to_rate;
            $temp['answer_rated'] = $tp->answer_rated;
            $temp['abnormalities'] = $tp->abnormalities;
            return $temp;
        })->all();
        $result4 = $result1->testParticipants->sortBy('id')->map(function ($tp) {
            $temp = [];
            $temp['active'] = $tp->active;
            $temp['answer_to_rate'] = $tp->answer_to_rate;
            $temp['answer_rated'] = $tp->answer_rated;
            $temp['abnormalities'] = $tp->abnormalities;
            return $temp;
        })->all();

        //todo result is different, because of filtering of discussingQuestion in the original controller method.

        dd( $result3, $result4, $benchmark,);

    }

    /** @test */
    public function testAbnormalities()
    {
        $result = 0;

        $benchmark = Benchmark::measure(function () use (&$result){
            $result = CoLearningHelper::getAbnormalitiesQuery()->get();
        });

        dd($result, $benchmark);
    }


    /** @test */
    public function testAbnormalitiesAndTestParticipants()
    {
        //j COMBINE abnormalities (JOIN) with TestParticipants

        $testParticipants = CoLearningHelper::fullTestParticipantsQuery(19, 241);

        dd($testParticipants->get());
    }

    protected function handleQueryLog(&$benchmark, $index)
    {
        $queryLog = collect(DB::getQueryLog());

        $benchmark[$index]['query-time'] = $queryLog->sum('time');
        $benchmark[$index]['queries'] = $queryLog->count();

        DB::flushQueryLog();
    }
}