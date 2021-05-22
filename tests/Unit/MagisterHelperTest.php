<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\MagisterHelper;
use tcCore\Http\Helpers\RTTIImportHelper;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\User;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;
use Tests\TestCase;

class MagisterHelperTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

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
    public function check_import_success_response_and_records_count_users_and_classes()
    {
        list($schoolLocation, $processResult) = $this->runMagisterImport();
        $this->assertStringContainsString(
            'De import was succesvol.',
            $processResult['data']
        );

        // letop 10 docenten betekend 10 teacher records.
        // In de uwlr Magister set zitten 5 klassen die geen leerlingen/docenten bevatten deze worden ook niet aangemaakt;
//        $this->assertStringContainsString(
//            'Er zijn 22 leerlingen aangemaakt, 10 docenten en 5 klassen.',
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

        // de import bevat 10 groepen; maar slechts 6 daarvan komen voor bij zowel leerlingen als docenten.
        $this->assertEquals(6, $schoolLocation->schoolClasses()->count());
        // de import bevat 4 samengestelde groepen maar slecht 3 bevatten leerlingen en docenten
        $this->assertEquals(3, $schoolLocation->schoolClasses()->where('is_main_school_class', 0)->count());
        // de import bevat 6 groepen maar slechts 3 bevatten leerlingen en docenten.
        $this->assertEquals(3, $schoolLocation->schoolClasses()->where('is_main_school_class', 1)->count());
    }

    /** @test */
//    public function it_should_import_users_with_eckId()
//    {
//        User::byEckId(function($user){
//            return $use
//        })
//
//        list($schoolLocation, $processResult) = $this->runMagisterImport();
//
//
//
//
//    }


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

    /**
     * @return array
     * @throws \Exception
     */
    private function runMagisterImport(): array
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
        return array($schoolLocation, $processResult);
    }


}
