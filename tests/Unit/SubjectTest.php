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

        $this->assertTrue(false, __CLASS__ . '::' . __METHOD__ . 'should have thrown an exception');
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

        $this->assertTrue(false, __CLASS__ . '::' . __METHOD__ . 'should have thrown an exception');
    }

    /**
     * @test
     * @dataProvider schoolLocationCustomerCodesDataSet
     */
    public function get_available_subjects_for_creathlon_school_location($customerCode)
    {
        $obj = new Subject();
        $args = [SchoolLocation::whereCustomerCode($customerCode)->first()];

        $args = array_filter($args);
        if(empty($args)) {
            $this->markTestSkipped(sprintf('%s: SchoolLocation with customer_code: "%s" is not available', __METHOD__, $customerCode));
        }

        $result = $this->callPrivateMethod($obj, 'getAvailableSubjectsForSchoolLocation', $args);

        $this->assertTrue(
            $result->count() > 0
        );
    }

    /**
     * @test
     * @dataProvider schoolLocationCustomerCodesDataSet
     */
    public function get_valid_subjects_of_another_school_location_for_a_user_filtered_on_allowed_valid_base_subjects_for_this_user($customerCode)
    {
        $obj = new Subject();
        $args = [
            Subject::query(),
            User::find(1486),
            SchoolLocation::whereCustomerCode($customerCode)->first()
        ];

        if($args[2] === null) {
            $this->markTestSkipped(sprintf('%s: SchoolLocation with customer_code: "%s" is not available', __METHOD__, $customerCode));
        }

        $result = $this->callPrivateMethod($obj, 'filterByUserAndSchoolLocation', $args)->get();

        $this->assertTrue($result->count() > 0);
    }

    public function schoolLocationCustomerCodesDataSet()
    {
        return [
            'creathlon' => ['CREATHLON'],
            'olympiade' => ['SBON1'],
            'olympiade_archive' => ['SBON'],
            'exam'      => ['OPENSOURCE1'],
            'cito'      => ['CITO-TOETSENOPMAAT'],
            'national'  => ['TBNI'],
        ];
    }
}
