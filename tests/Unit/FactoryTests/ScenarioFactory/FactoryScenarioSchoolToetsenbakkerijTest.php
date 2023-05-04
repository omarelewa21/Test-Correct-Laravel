<?php

namespace Tests\Unit\FactoryTests\ScenarioFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\FactoryScenarios\FactoryScenarioSchoolToetsenbakkerij;
use tcCore\SchoolLocation;
use Tests\TestCase;

class FactoryScenarioSchoolToetsenbakkerijTest extends TestCase
{


    /** @test */
    public function can_create_toetsenbakker_school_with_factory()
    {
        FactoryScenarioSchoolToetsenbakkerij::create();

        $schoolLocation = SchoolLocation::orderBy('id', 'desc')->first();

        $this->assertEquals(config('custom.TB_customer_code'), $schoolLocation->customer_code);
        $this->assertEquals(SchoolLocation::LICENSE_TYPE_CLIENT, $schoolLocation->license_type);
    }
}