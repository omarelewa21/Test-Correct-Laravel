<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeRated;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Period;
use tcCore\Subject;
use tcCore\User;
use Tests\TestCase;

class PValueRepositoryTest extends TestCase
{
//    use DatabaseTransactions;


    /** @test */
    public function it_can_load_p_value_stats_by_subject_for_a_student()
    {
        $this->withoutExceptionHandling();

//        $factory = FactoryScenarioTestTakeRated::create($this->getTeacherOne());
        $studentOne = $this->getStudentOne();
        $this->assertEmpty($studentOne->pValueStatsForAllSubjects);
        $studentOne->loadPValueStatsForAllSubjects();
//        dd($studentOne->pValueStatsForAllSubjects);
        $this->assertArrayHasKey('Nederlands', $studentOne->pValueStatsForAllSubjects);
    }

    /** @test */
    public function if_a_students_gets_a_new_subject_it_should_also_see_that_subject_in_the_subject_graph()
    {
        $this->withoutExceptionHandling();

//        $factory = FactoryScenarioTestTakeRated::create($this->getTeacherOne());
        $studentOne = $this->getStudentOne();

        $pValues = PValueRepository::getPValueForStudentBySubject(
            $studentOne,
            collect(),
            collect(),
            collect(),
        );

        $this->assertCount(1, $pValues);

        $this->createBiologySectionWithClassForStudentOneAndTeacherOneInCurrentSchoolYear();

        $pValuesWithBiology = PValueRepository::getPValueForStudentBySubject(
            $studentOne,
            collect(),
            collect(),
            collect(),
        );

        $this->assertCount(2,  $pValuesWithBiology);
    }

    /** @test */
    public function it_should_fail_when_load_is_called_without_Data_value_stats_by_subject_for_a_student()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $this->assertEmpty($studentOne->pValueStatsForAllSubjects);
        $studentOne->loadPValueStatsForAllSubjects();
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_by_subject()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $firstRecord = PValueRepository::getPValueForStudentBySubject($studentOne, collect(), collect(), collect())->first();
        $this->assertEquals('Nederlands', $firstRecord->serie);
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_by_subject_filtered_by_educationLevelYear()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $firstRecord = PValueRepository::getPValueForStudentBySubject(
            $studentOne,
            collect(),
            collect([['id' => 1]]),
            collect(),
        )->first();
        $this->assertEquals('Nederlands', $firstRecord->serie);

        // test met een eductionLevelYear that doesnot exists;
        $this->assertEmpty(
            PValueRepository::getPValueForStudentBySubject(
                $studentOne,
                collect(),
                collect([['id' => 2]]),
                collect(),
            )
        );
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_by_subject_filtered_by_teacher()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $firstRecord = PValueRepository::getPValueForStudentBySubject(
            $studentOne,
            collect(),
            collect(),
            collect([$this->getTeacherOne()])
        )->first();
        $this->assertEquals('Nederlands', $firstRecord->serie);
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_by_subject_filtered_by_Period()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $firstRecord = PValueRepository::getPValueForStudentBySubject(
            $studentOne,
            Period::where('id', 1)->get(),
            collect(),
            collect()

        )->first();
        $this->assertEquals('Nederlands', $firstRecord->serie);
    }


//        $response = static::get(
//            $this->authStudentOneGetRequest(
//                'api-c/user/' . $this->getStudentOne()->uuid,
//                $this->getStudentOneAuthRequestData([
//                    "with" => [
//                        "studentAverageGraph",
//                        "studentSubjectAverages",
//                        "testsParticipated"
//                    ]
//                ])
//            )
//        );
//        dd(json_decode($response->getContent()));

    /** @test */
    public function it_can_report_p_values_for_a_student_per_attainments()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $pValuesPerAttainment = PValueRepository::getPValuePerAttainmentForStudent(
            $studentOne,
            collect(),
            collect(),
            collect(),
            Subject::find(1),
           false
        );
        $this->assertCount(1, $pValuesPerAttainment->filter(fn($q) => $q->serie === 'Schrijfvaardigheid'));
        $this->assertCount(41, $pValuesPerAttainment);
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_per_learning_goal()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $pValuesPerAttainment = PValueRepository::getPValuePerAttainmentForStudent(
            $studentOne,
            collect(),
            collect(),
            collect(),
            Subject::find(1),
           true
        );

        $this->assertCount(0, $pValuesPerAttainment);
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_and_a_attainment_per_sub_attainments()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $attainment = Attainment::find(5); //Literatuur (id: 5) has sub-attainments 410, 411, 412

        $pValuesPerAttainment = PValueRepository::getPValuePerSubAttainmentForStudentAndAttainment(
            $studentOne,
            $attainment,
            collect(),
            collect(),
            collect()
        );
        $this->assertGreaterThanOrEqual(1, $pValuesPerAttainment->filter(function($query) {
            return in_array($query->serie, ['Literaire ontwikkeling', 'Literaire begrippen', 'Literatuurgeschiedenis']);
        })->count());
    }

    private function createBiologySectionWithClassForStudentOneAndTeacherOneInCurrentSchoolYear()
    {
        // creeer een sectie in de huidige schoollocatie voor het vak biologie;
        $this->actingAs($this->getStudentOne());
        $schoolYearLocation = SchoolYearRepository::getCurrentSchoolYear();

        $sectionFactory = FactorySection::create($this->getStudentOne()->schoolLocation, 'Biologie Sectie voor school 1');
        $sectionFactory->addSubject(
            BaseSubject::where('name', 'biologie')->first(),
            'Biologie'
        );

        FactorySchoolClass::create($schoolYearLocation, 1, 'biologie_klas')
            ->addTeacher($this->getTeacherOne(), $sectionFactory->section->subjects()->where('name', 'biologie')->first())
            ->addStudent($this->getStudentOne());

    }
}