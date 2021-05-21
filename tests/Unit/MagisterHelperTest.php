<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\MagisterHelper;
use tcCore\Http\Helpers\RTTIImportHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\OnboardingWizardUserStep;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Section;
use tcCore\Shortcode;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class MagisterHelperTest extends TestCase
{
   use DatabaseTransactions;

    /** @test */
    public function test_guzzle()
    {
        $this->assertCount(0, UwlrSoapResult::all());
        $this->assertCount(0, UwlrSoapEntry::all());

        $helper = (new MagisterHelper)
            ->parseResult()
            ->storeInDB();

        $this->assertInstanceOf(MagisterHelper::class, $helper);

        $this->assertCount(1, UwlrSoapResult::all());
        $this->assertCount(39, UwlrSoapEntry::all());

        $result = UwlrSoapResult::first();
        $result->client_code = 'Magister';
        $result->client_name = 'UNIT_TEST';
        $result->school_year = '20-21';
    }

    /** @test */
    public function FeedUwlrSoapResult()
    {

        Auth::loginUsingId(755);
        (new MagisterHelper)
            ->parseResult()
            ->storeInDB();

        $result = UwlrSoapResult::first();
        $helper = RTTIImportHelper::initWithUwlrSoapResult($result, 'sobit.nl');

        $usersCountBefore = User::count();
        $teacherCountBefore = Teacher::count();

        $schoolLocation = SchoolLocation::where('external_main_code', '99DE')->first();


        $processResult = $helper->process();
        $this->assertStringContainsString(
            'De import was succesvol.',
            $processResult['data']
        );

//        $this->assertStringContainsString(
//            'Er zijn 22 leerlingen aangemaakt, 5 docenten en 10 klassen.',
//            $processResult['data']
//        );

        $this->assertEquals(28, $schoolLocation->users()->where('demo',0)->count());
        // de import bevat 22 leerlingen
        $this->assertEquals(22, $schoolLocation->users->filter(function ($user) {
            return $user->isA('student') && $user->demo === 0;
        })->count());
        // de import bevat 6 leerkrachten;
        $this->assertEquals(6, $schoolLocation->users->where('demo', 0)->filter(function ($user) {
            return $user->isA('teacher') && $user->demo === 0;
        })->count());
dd($schoolLocation->schoolClasses->map(function($klas){
    return $klas->name;
}));
        // de import bevat 10 groepen;
        $this->assertEquals(10, $schoolLocation->schoolClasses()->count());
        // de import bevat 4 samengestelde groepen
        $this->assertEquals(4, $schoolLocation->schoolClasses()->where('is_main_school_class', 0)->count());
        // de import bevat 6 groepen;
        $this->assertEquals(6, $schoolLocation->schoolClasses()->where('is_main_school_class', 1)->count());
    }


    /** @test */
    public function uwlrSoapResultToCVs()
    {
        $helper = (new MagisterHelper)
            ->parseResult()
            ->storeInDB();

        UwlrSoapResult::first()->toCVS();
    }

    /** @test */
    public function test_service()
    {
        dd(MagisterHelper::guzzle());

    }


}
