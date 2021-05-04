<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
//    use DatabaseTransactions;

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
        (new MagisterHelper)
            ->parseResult()
            ->storeInDB();

        $result = UwlrSoapResult::first();

        $helper = RTTIImportHelper::initWithUwlrSoapResult($result, 'sobit.nl');

        dd($helper->process());
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
