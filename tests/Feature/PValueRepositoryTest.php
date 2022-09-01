<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRepository;
use tcCore\Period;
use Tests\TestCase;

class PValueRepositoryTest extends TestCase
{
    use DatabaseTransactions;


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
        $firstRecord = PValueRepository::getPValueForStudentBySubject($studentOne)->first();
        $this->assertEquals('Nederlands', $firstRecord->subject);
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_by_subject_filtered_by_educationLevelYear()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $firstRecord = PValueRepository::getPValueForStudentBySubject(
            $studentOne,
            null,
            collect([['id' => 1]]),
            null,
        )->first();
        $this->assertEquals('Nederlands', $firstRecord->subject);

        // test met een eductionLevelYear that doesnot exists;
        $this->assertEmpty(
            PValueRepository::getPValueForStudentBySubject(
                $studentOne,
                null,
                collect([['id' => 2]]),
                null,
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
            null,
            null,
            collect([$this->getTeacherOne()])
        )->first();
        $this->assertEquals('Nederlands', $firstRecord->subject);
    }

    /** @test */
    public function it_can_report_p_values_for_a_student_by_subject_filtered_by_Period()
    {
        $this->withoutExceptionHandling();
        $studentOne = $this->getStudentOne();
        $firstRecord = PValueRepository::getPValueForStudentBySubject(
            $studentOne,
            Period::where('id', 1)->get(),
            null,
            null

        )->first();
        $this->assertEquals('Nederlands', $firstRecord->subject);
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

}