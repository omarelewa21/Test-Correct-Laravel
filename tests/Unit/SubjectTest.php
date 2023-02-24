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
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    /** @test */
    public function filter_for_student_should_return_a_collection_of_subject_for_student_one()
    {
        $result = Subject::filterForStudent(ScenarioLoader::get('student1'));
        $set = $result->get();

        $this->assertCount(1, $set);
        $this->assertEquals('Nederlandse gramatica', $set->first()->name);
    }

    /** @test */
    public function filter_for_student_should_return_expeption_when_called_with_teacher()
    {
        try{
            Subject::filterForStudent(ScenarioLoader::get('teacher1'));
        } catch (\Exception $e) {
            $this->assertEquals(
                Subject::NOT_ALLOWED_FOR_TEACHER_EXCEPTION_MSG,
                $e->getMessage()
            );
            return;
        }

        $this->assertTrue(false, __CLASS__ . '::'. __METHOD__. 'should have thrown an exception');
    }

    /** @test */
    public function filter_for_student_current_school_year_should_return_a_collection_of_subjects_for_student_one()
    {

        $this->actingAs(ScenarioLoader::get('student1'));

        $result = Subject::filterForStudentCurrentSchoolYear(ScenarioLoader::get('student1'));
        $set = $result->get();

        $this->assertCount(1, $set);
        $this->assertEquals('Nederlandse gramatica', $set->first()->name);
    }

    /** @test */
    public function filter_for_student_current_school_year_should_return_expeption_when_called_with_teacher()
    {
        try{
            Subject::filterForStudentCurrentSchoolYear(ScenarioLoader::get('teacher1'));
        } catch (\Exception $e) {
            $this->assertEquals(
                Subject::NOT_ALLOWED_FOR_TEACHER_EXCEPTION_MSG,
                $e->getMessage()
            );
            return;
        }

        $this->assertTrue(false, __CLASS__ . '::'. __METHOD__. 'should have thrown an exception');
    }
}
