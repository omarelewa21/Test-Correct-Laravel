<?php

namespace Tests\Unit\FactoryTests\ScenarioFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\SchoolLocation;

class FactoryScenarioSchoolToetsenbakkerijTest extends \Tests\TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_create_toetsenbakker_school_with_factory()
    {
        $school = \tcCore\FactoryScenarios\FactoryScenarioSchoolToetsenbakkerij::create()->school;

        $schoolLocation = SchoolLocation::orderBy('id', 'desc')->first();

        $this->assertEquals(config('custom.TB_customer_code'), $schoolLocation->customer_code);
    }
}