<?php

namespace Tests\Unit;

use tcCore\LoginLog;
use tcCore\SchoolLocationReport;
use tcCore\SchoolLocation;
use Tests\TestCase;
use Carbon\Carbon;

class LocationReportTest extends TestCase
{

    //use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected function verbose_out($string) {

        fwrite(STDERR, print_r($string, TRUE));
    }

    /** @test */
    public function can_update_all_locations() {

        $nr_locations = SchoolLocation::distinct('id')->count();

        SchoolLocationReport::updateAllLocationStats();

        $nr_report_locations_updated = SchoolLocationReport::where('updated_at','>',Carbon::now()->subMinute(5))->count();

        // all locations are present and updated in the report
        $this->assertEquals($nr_locations, $nr_report_locations_updated);


    }

}
