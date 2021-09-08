<?php
namespace Tests\Unit\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\SurveillanceController;
use tcCore\SchoolClass;
use Tests\TestCase;

class SurveillanceControllerTest extends TestCase
{
    /** @test */
    public function test_Response()
    {
        Auth::login(self::getTeacherOne());

        $response = ((new SurveillanceController)->index());

        dd($response);
    }


    /** @test */
    public function takes_key_should_contain_progress()
    {
        Auth::login(self::getTeacherOne());

        $response = ((new SurveillanceController)->index());
        $firstKey = array_key_first($response['takes']);

        $this->assertStringContainsString('progress_', $firstKey);

        $progress = array_pop($response['takes']);
        $this->assertEquals(0, $progress);
    }

    /** @test */
    public function takes_key_should_contain_three_test_takes()
    {
        Auth::login(self::getTeacherOne());
        Carbon::setTestNow(Carbon::create(2018, 6, 18, 12, 10));

        $response = ((new SurveillanceController)->index());

        $this->assertCount(3, $response['takes']);
    }

    /** @test */
    public function time_key_should_reflect_current_time_in_24_hours_and_minutes()
    {
        Auth::login(self::getTeacherOne());
        Carbon::setTestNow(Carbon::create(2018, 6, 18, 12, 10));

        $response = ((new SurveillanceController)->index());

        $this->assertEquals('12:10', $response['time']);
    }
    /** @test */
    public function it_should_contain_the_correct_keys()
    {
        Auth::login(self::getTeacherOne());
        $response = ((new SurveillanceController)->index());

        collect(['takes', 'participants', 'time', 'alerts', 'ipAlerts'])->each(function($key) use ($response) {
            $this->assertArrayHasKey($key, $response);
        });
    }

}
