<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\MagisterHelper;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\User;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;
use Tests\TestCase;

class MagisterHelperTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    /** @test */
    public function test_guzzle()
    {
        $this->assertCount(0, UwlrSoapResult::all());
        $this->assertCount(0, UwlrSoapEntry::all());

        $helper = (new MagisterHelper)
            ->parseResult()
            ->storeInDB("99DE", "00");

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
//dd($processResult);
        // letop 10 docenten betekend 10 teacher records.
        // In de uwlr Magister set zitten 5 klassen die geen leerlingen/docenten bevatten deze worden ook niet aangemaakt;
        $this->assertStringContainsString(
            'Er zijn 22 leerlingen aangemaakt, 12 docenten en 5 klassen.',
            $processResult['data']
        );

        $this->assertEquals(28, $schoolLocation->users()->where('demo', 0)->count());
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
    public function it_should_import_users_with_eckId_and_add_email_addresses()
    {
        $this->assertNull(User::findByEckId('eckid_L1')->first());
        $this->assertNull(User::findByEckId('eckid_T1')->first());

        list($schoolLocation, $processResult) = $this->runMagisterImport();

        $this->assertNotNull($student = User::findByEckId('eckid_L1')->first());
        $this->assertTrue($student->isA('student'));
        $this->assertEquals(
            sprintf(User::STUDENT_IMPORT_EMAIL_PATTERN, $student->id),
            $student->username
        );

        $this->assertNotNull($teacher = User::findByEckId('eckid_T1')->first());
        $this->assertTrue($teacher->isA('teacher'));
        $this->assertEquals(
            sprintf(User::TEACHER_IMPORT_EMAIL_PATTERN, $teacher->id),
            $teacher->username
        );
    }

    /** @test */
    public function it_should_import_users_with_password_when_not_on_production()
    {
        list($schoolLocation, $processResult) = $this->runMagisterImport();

        $this->assertNotNull($student = User::findByEckId('eckid_L1')->first());
        $this->assertTrue($student->isA('student'));
        $this->assertTrue(
            Hash::check(
                sprintf(User::STUDENT_IMPORT_PASSWORD_PATTERN, $student->id),
                $student->password
            )
        );

        $this->assertNotNull($teacher = User::findByEckId('eckid_T1')->first());
        $this->assertTrue($teacher->isA('teacher'));
        $this->assertTrue(
            Hash::check(
                sprintf(User::TEACHER_IMPORT_PASSWORD_PATTERN, $teacher->id),
                $teacher->password
            )
        );


    }

    /** @test */
    public function it_should_import_classes_without_an_education_level_id()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->first();
        $this->assertCount(0, SchoolClass::where('school_location_id', $location->getKey())->get());
        list($schoolLocation, $processResult) = $this->runMagisterImport();
        $importedClasses = SchoolClass::where('school_location_id', $location->getKey())->get();
        $this->assertEquals(1, $importedClasses->first()->education_level_id); // is vwo;
        $this->assertEquals(1, $importedClasses->first()->education_level_year);
    }

    /** @test */
    public function it_should_create_teacher_users_that_are_already_verified()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->first();
        $teachers = User::where([['school_location_id', $location->getKey()], ['demo', 0]])->get()->filter(function (
            $user
        ) {
            return $user->isA('teacher');
        });
        $this->assertCount(0, $teachers);
        list($schoolLocation, $processResult) = $this->runMagisterImport();

        $teachersAfterImport = User::where([
            ['school_location_id', $location->getKey()], ['demo', 0]
        ])->get()->filter(function ($user) {
            return $user->isA('teacher') && $user->account_verified;
        });

        $this->assertCount(6, $teachersAfterImport);
    }
//    /** @test */
// Disabled because of discussion name_suffix is voorvoegsel of tussenvoegsel. (t bekend het eerste maar t wordt nu gebruikt als het laatste.
//    public function student_demo_10_should_have_a_name_suffix_of_a()
//    {
//        list($schoolLocation, $processResult) = $this->runMagisterImport();
//        dd($processResult);
//        $demo10 = User::findByEckId('eckid_L10')->first();
//        $this->assertEquals('a', $demo10->name_suffix);
//
//
//    }

    public function is_should_be_able_to_add_multiple_samengestelde_groepen_als_klassen_aan_leerlingen()
    {
        //Leerling Demo10 zit in H1C, H1Sport en H1Muziek in de XML,
        // maar dit zie ik niet terug in TC (daar alleen aan H1C gekoppeld).
        // Ook Demo11 en Demo13 heeft maar 1 van de 3 klassen gekoppeld.
        // Misschien gaat dit mis omdat die aan meer dan 1 samengestelde groep zijn gekoppeld?
//        list($schoolLocation, $processResult) = $this->runMagisterImport();
//        (User::findByEckId('eckid_L10')->first()->classes);



    }


    /** @test */
//    public function uwlrSoapResultToCSV()
//    {
//        $helper = (new MagisterHelper)
//            ->parseResult()
//            ->storeInDB();
//
//        UwlrSoapResult::first()->toCSV();
//    }

//    /** @test */
//    public function test_service()
//    {
//        dd(MagisterHelper::guzzle());
//
//    }

    /**
     * @return array
     * @throws \Exception
     */
    private function runMagisterImport(): array
    {
        Auth::loginUsingId(755);
        (new MagisterHelper)
            ->parseResult()
            ->storeInDB("99DE", "00");

        $result = UwlrSoapResult::first();
        $helper = ImportHelper::initWithUwlrSoapResult($result, 'sobit.nl');

        $usersCountBefore = User::count();
        $teacherCountBefore = Teacher::count();

        $schoolLocation = SchoolLocation::where('external_main_code', '99DE')->first();

        $processResult = $helper->process();
        return array($schoolLocation, $processResult);
    }


    protected function tearDown(): void
    {
//        UwlrSoapEntry::deleteMagisterData();
        parent::tearDown();
    }


}
