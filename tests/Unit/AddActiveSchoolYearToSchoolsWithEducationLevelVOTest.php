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
use tcCore\Http\Helpers\AddActiveSchoolYearHelper;
use tcCore\Period;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;

class AddActiveSchoolYearToSchoolsWithEducationLevelVOTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function it_should_return_vo_school_locations_only()
    {
        $set = SchoolLocation::voOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(5, $set);

    }

    /** @test */
    public function it_should_return_without_a_schoolyear()
    {
        $set = SchoolLocation::withoutSchoolYear('1975')->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(10, $set);
    }

    /** @test */
    public function it_should_return_active_only()
    {
        $set = SchoolLocation::activeOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(10, $set);

        SchoolLocation::first()->setAttribute('activated', 0)->save();

        $set = SchoolLocation::activeOnly()->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(9, $set);
    }

    /** @test */
    public function it_should_return_a_small_collection_because_most_schools_have_access_to_school_year_2020()
    {
        $set = SchoolLocation::withoutSchoolYear('2020')->get()->filter(function ($schoolLocation) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(6, $set);
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
        $this->assertEquals('2021-08-01', $period->start_date);
        $this->assertEquals('2022-07-31', $period->end_date);

    }

    /** @test */
    public function it_should_return_without_school_year_active_only_of_type_vo()
    {
        //SchoolLocation::withoutSchoolYear('2020')->activeOnly()->voOnly()->dd();

        $set = SchoolLocation::withoutSchoolYear('2020')->activeOnly()->voOnly()->get()->filter(function (
            $schoolLocation
        ) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(4, $set);
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

        // er zitten 5 scholen in die van een nieuwe periode moeten worden voorzien;

        $this->assertEquals(
            $startCountPeriod + 3,
            Period::count()
        );

        $this->assertEquals(
            $startCountSchoolYear + 3,
            SchoolYear::count()
        );
    }

    /** @test */
    public function it_should_select_all_school_locations_without_a_period_at_a_certain_date()
    {
        $set = SchoolLocation::NoActivePeriodAtDate('1-1-1992')->activeOnly()->voOnly()->get()->filter(function (
            $schoolLocation
        ) {
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(0, $set);
    }

    /** @test */
    public function it_should_return_a_correct_query()
    {
        $builder = SchoolLocation::NoActivePeriodAtDate('2021-08-01')->activeOnly();

        $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
        $query = vsprintf($query, $builder->getBindings());
        $this->assertEquals(
            "select * from `school_locations` where `id` not in (select `school_location_id` from `periods` inner join `school_years` on `school_year_id` = `school_years`.`id` inner join `school_location_school_years` on `school_location_school_years`.`school_year_id` = `school_years`.`id` where (`start_date` >= '2021-08-01 00:00:00' or `end_date` >= '2021-08-01 00:00:00') and `periods`.`deleted_at` is null) and `activated` = '1' and `school_locations`.`deleted_at` is null",
            $query
        );

    }



    /** @test */
    public function when_running_the_command_it_should_add_5_periods_and_school_years()
    {
        $startCountPeriod = Period::count();
        $startCountSchoolYear = SchoolYear::count();

        Artisan::call('school_locations:add_new_period ');

        // er zitten 11 scholen in die van een nieuwe periode moeten worden voorzien;

        $this->assertEquals(
            $startCountPeriod + 7,
            Period::count()
        );

        $this->assertEquals(
            $startCountSchoolYear + 7,
            SchoolYear::count()
        );
    }

}
