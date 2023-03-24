<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Carbon\Carbon;
use Composer\Repository\ArtifactRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\EckidUser;
use tcCore\EducationLevel;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\AddActiveSchoolYearHelper;
use tcCore\Period;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class AddActiveSchoolYearToSchoolsWithEducationLevelVOTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private User $teacherOne;
    private User $studentOne;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
    }


    /** @test */
    public function it_should_return_vo_school_locations_only()
    {
        $set = SchoolLocation::voOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(1, $set);

        $schoolLocation = $set->first();
        $schoolLocation->educationLevels()->detach();

        $newSet = SchoolLocation::voOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(0, $newSet);

        $this->assertEquals(0, $schoolLocation->educationLevels()->count());
        $schoolLocation->educationLevels()->attach(EducationLevel::where('name', 'MBO-N1')->first());
        $this->assertEquals(1, $schoolLocation->educationLevels()->count());

        $setAfterAddingMBO = SchoolLocation::voOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(0, $newSet);
    }

    /** @test */
    public function it_should_return_without_a_schoolyear()
    {
        $set = SchoolLocation::withoutSchoolYear('1975')->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(1, $set);
    }

    /** @test */
    public function it_should_return_active_only()
    {
        $set = SchoolLocation::activeOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(1, $set);

        SchoolLocation::first()->setAttribute('activated', 0)->save();

        $set = SchoolLocation::activeOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(0, $set);
    }

    /** @test */
    public function it_should_return_a_small_collection_because_most_schools_have_access_to_school_year_2020()
    {
        $set = SchoolLocation::withoutSchoolYear('2020')->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(1, $set);
    }

    /**
     * @test
     * Kijk voor welke schoollocaties nog geen school_year.year = 2021 is aangemaakt.
     * Maak vervolgens een school_year 2021 aan  en een record in periods “2021-2022“,
     * lopend van 1-8-2021 tm 31-7-2022 en daaraan gekoppeld
     * een record in school_location_school_years.
     */


    /** @test */
    public function it_should_add_a_school_year_and_a_period_to_a_school_location()
    {
        $location = SchoolLocation::find(1);
        $this->assertCount(0, $location->schoolYears->where('year', '2021'));

        $user = $location->users()->first();

        Auth::login($user);

        $location->addSchoolYearAndPeriod('2021', '01-08-2021', '31-07-2022');

        $schoolYears = $location->refresh()->schoolYears->where('year', '2021');

        $this->assertCount(1, $schoolYears);

        $periods = $schoolYears->first()->periods;

        $this->assertCount(1, $periods);
        $period = $periods->first();

        $dateStart = Carbon::parse('2021-08-01');
        $dateEnd = Carbon::parse('2022-07-31');
        $periodDateStart = Carbon::parse($period->start_date);
        $periodDateEnd = Carbon::parse($period->end_date);


        $this->assertTrue($dateStart->eq($periodDateStart));
        $this->assertTrue($dateEnd->eq($periodDateEnd));
    }

    /** @test */
    public function it_should_return_without_school_year_active_only_of_type_vo()
    {
        //SchoolLocation::withoutSchoolYear('2020')->activeOnly()->voOnly()->dd();

        $set = SchoolLocation::withoutSchoolYear('2020')
            ->activeOnly()
            ->voOnly()
            ->get()
            ->filter(function ($schoolLocation) {
                return $schoolLocation instanceof SchoolLocation;
            });
        $this->assertCount(1, $set);

        $user = $set->first()->users()->first();
        Auth::login($user);

        $set->first()->addSchoolYearAndPeriod('2020', '01-08-2020', '31-07-2021');


        $newSet = SchoolLocation::withoutSchoolYear('2020')
            ->activeOnly()
            ->voOnly()
            ->get()
            ->filter(function ($schoolLocation) {
                return $schoolLocation instanceof SchoolLocation;
            });
        $this->assertCount(0, $newSet);
    }

    /** @test */
    public function it_should_have_a_command_to_add_school_school_years_and_periods_to_schools_of_type_po()
    {
        $startCountPeriod = Period::count();
        $startCountSchoolYear = SchoolYear::count();


        SchoolLocation::withoutSchoolYear('2021')->activeOnly()->voOnly()->get()->each(function ($location) {
            $user = $location->users()->first();
            Auth::login($user);

            $location->addSchoolYearAndPeriod('2021', '01-08-2021', '31-07-2022');
        });

        // er zit 1  school in die van een nieuwe periode en schooljaar moet worden voorzien;

        $this->assertEquals(
            $startCountPeriod + 1,
            Period::count()
        );

        $this->assertEquals(
            $startCountSchoolYear + 1,
            SchoolYear::count()
        );
    }

    /** @test */
    public function it_should_select_all_school_locations_without_a_period_at_a_certain_date()
    {
        $this->markTestSkipped('underlying query is not correct in sqlLite context');
        $set = SchoolLocation::NoActivePeriodAtDate('1-1-1992')
            ->activeOnly()
            ->voOnly()
            ->get()
            ->filter(function ($schoolLocation) {
                return $schoolLocation instanceof SchoolLocation;
            });
        $this->assertCount(0, $set);
    }

    /** @test */
    public function it_should_return_a_correct_query()
    {
        $this->markTestSkipped('underlying query is not correct in sqlLite context');
        $builder = SchoolLocation::NoActivePeriodAtDate('2021-08-01')->activeOnly();

        $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
        $query = vsprintf($query, $builder->getBindings());
        $this->assertEquals(
            "select * from `school_locations` where `id` not in (select `school_location_id` from `periods` inner join `school_years` on `school_year_id` = `school_years`.`id` inner join `school_location_school_years` on `school_location_school_years`.`school_year_id` = `school_years`.`id` where (`start_date` >= '2021-08-01 00:00:00' or `end_date` >= '2021-08-01 00:00:00') and `periods`.`deleted_at` is null) and `activated` = '1' and `school_locations`.`deleted_at` is null",
            str_replace('"', "`", $query)
        );

    }


    /** @test */
    public function when_running_the_command_it_should_add_1_period_and_1_school_year()
    {
        $this->markTestSkipped('underlying query is not correct in sqlLite context');
        $startCountPeriod = Period::count();
        $startCountSchoolYear = SchoolYear::count();

        Artisan::call('school_locations:add_new_period ');

        // er zitten 11 scholen in die van een nieuwe periode moeten worden voorzien;

        $this->assertEquals(
            $startCountPeriod + 1,
            Period::count()
        );

        $this->assertEquals(
            $startCountSchoolYear + 1,
            SchoolYear::count()
        );
    }

}
