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
        $testTakeUuid = '779168f6-4afe-4153-9102-9a131134ffa7';

        $testTake = TestTake::whereUuid($testTakeUuid)->first();

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

        $benchmark[2]['time'] = Benchmark::measure(function () use (&$result2, $testTakeId, $user) {
            return $result2 = CoLearningHelper::getTestParticipantsWithStatus($testTakeId, $user->id);
        });

        $this->handleQueryLog($benchmark, 2);


        dd($benchmark);
        dd(
            $result1,
            $result2,
        );
    }


    protected function handleQueryLog(&$benchmark, $index)
    {
        $queryLog = collect(DB::getQueryLog());

        $benchmark[$index]['query-time'] = $queryLog->sum('time');
        $benchmark[$index]['queries'] = $queryLog->count();

        DB::flushQueryLog();
    }
}