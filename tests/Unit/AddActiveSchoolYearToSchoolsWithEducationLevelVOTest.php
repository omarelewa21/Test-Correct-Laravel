<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\EckidUser;
use tcCore\Http\Helpers\AddActiveSchoolYearHelper;
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
        $set = SchoolLocation::onlyVo()->get()->filter(function($schoolLocation){
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(5, $set);

    }

    /** @test */
    public function it_should_return_without_a_schoolyear()
    {
        $set = SchoolLocation::withoutSchoolYear('2021')->get()->filter(function($schoolLocation){
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(11, $set);
    }

    /** @test */
    public function it_should_return_a_small_collection_because_most_schools_have_access_to_school_year_2020()
    {
        $set = SchoolLocation::withoutSchoolYear('2020')->get()->filter(function($schoolLocation){
            return $schoolLocation instanceof SchoolLocation;
        });
        $this->assertCount(4, $set);
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



}
