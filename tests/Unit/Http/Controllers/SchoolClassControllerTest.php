<?php

namespace Tests\Unit\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\SchoolYear;
use tcCore\Subject;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class SchoolClassControllerTest extends TestCase
{

    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('user');
    }

    /** @test */
    public function when_no_current_school_year_available_filter_should_return_empty()
    {
        Period::get()->map->delete();
        SchoolYear::get()->map->delete();
        $response = $this->get(static::authUserGetRequest('school_class',
            [
                'mode'   => 'list',
                'filter' => ['current' => '1']
            ], $this->user
        )
        )->assertStatus(200);

        $this->assertEquals(0, count($response->original));
    }


    /** @test */
    public function filter_current_returns_current_classes()
    {
        $lastYear = (int) Carbon::today()->subYear()->format('Y');

        $schoolYearLocationLastYear = FactorySchoolYear::create($this->user->schoolLocation, $lastYear)
            ->addPeriodFullYear()->schoolYear;
        $schoolClassFactory = FactorySchoolClass::create($schoolYearLocationLastYear)
            ->addTeacher($this->user, Subject::first());

        $response = $this->get(static::authUserGetRequest('school_class',
            [
                'mode'   => 'list',
                'filter' => ['current' => '1']
            ], $this->user
        )
        )->assertStatus(200);

        $this->assertEquals(1, count($response->original));
    }

    /** @test */
    public function filter_current_false_returns_all_classes()
    {
        $lastYear = (int) Carbon::today()->subYear()->format('Y');

        $schoolYearLocationLastYear = FactorySchoolYear::create($this->user->schoolLocation, $lastYear)
            ->addPeriodFullYear()->schoolYear;
        FactorySchoolClass::create($schoolYearLocationLastYear)
            ->addTeacher($this->user, Subject::first());

        $response = $this->get(static::authUserGetRequest('school_class',
            [
                'mode'   => 'list',
                'filter' => ['current' => '0']
            ], $this->user
        )
        )->assertStatus(200);
        $this->assertEquals(2, count($response->original));
    }

    /** @test */
    public function demo_class_after_create_has_do_not_overwrite_from_interface_set_to_true()
    {
        $schoolClass = factory(SchoolClass::class, 1)->make([
            'demo'                            => true,
            'do_not_overwrite_from_interface' => false
        ])->first();
        $schoolClass->save();
        $this->assertEquals(true, $schoolClass->do_not_overwrite_from_interface);
    }

    /** @test */
    public function not_demo_class_after_create_has_do_not_overwrite_from_interface_set_to_false()
    {
        $schoolClass = factory(SchoolClass::class, 1)->make([
            'demo'                            => false,
            'do_not_overwrite_from_interface' => false
        ])->first();
        $schoolClass->save();
        $this->assertEquals(false, $schoolClass->do_not_overwrite_from_interface);
    }

    /** @test */
    public function demo_class_after_create_has_do_not_overwrite_from_interface_set_to_true_after_update()
    {
        $schoolClass = factory(SchoolClass::class, 1)->make([
            'demo'                            => true,
            'do_not_overwrite_from_interface' => false
        ])->first();
        $schoolClass->save();
        $schoolClass->do_not_overwrite_from_interface = false;
        $schoolClass->save();
        $schoolClassToTest = SchoolClass::find($schoolClass->id)->first();
        $this->assertEquals(true, $schoolClassToTest->do_not_overwrite_from_interface);
    }

}
