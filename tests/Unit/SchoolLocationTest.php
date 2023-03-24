<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\EckidUser;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Manager;
use tcCore\Mentor;
use tcCore\Period;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Student;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class SchoolLocationTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    /** @test */
    public function it_should_return_all_periods()
    {
        $this->assertCount(
            2,
            $periods = ScenarioLoader::get('student1')->schoolLocation->getPeriods()
        );

        $this->assertInstanceOf(Period::class, $periods->first());
    }
}
