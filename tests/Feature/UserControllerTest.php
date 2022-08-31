<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\BaseSubject;
use tcCore\Exceptions\Handler;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeRated;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Period;
use tcCore\Subject;
use tcCore\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
//    use DatabaseTransactions;

    /** @test */
    public function a_teacher_cannot_be_added_if_no_current_active_period()
    {
        $this->withoutExceptionHandling();
        \tcCore\SchoolLocationSchoolYear::where('school_location_id', 2)->orderby('created_at', 'desc')->first()->delete();

        $data = [
            'school_location_id' => '2',
            'name_first'         => 'a',
            'name_suffix'        => '',
            'name'               => 'bc',
            'abbreviation'       => 'abcc',
            'username'           => 'abc@test-correct.nl',
            'password'           => 'aa',
            'external_id'        => 'abc',
            'note'               => '',
            'user_roles'         => [1],
        ];

        $response = $this->post(
            route('user.store'),
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );

        $response->assertStatus(422);
        $rData = $response->decodeResponseJson();
        $this->assertEquals('U kunt een docent pas aanmaken nadat u een actuele periode heeft aangemaakt. Dit doet u door als schoolbeheerder in het menu Database -> Schooljaren een schooljaar aan te maken met een periode die in de huidige periode valt.',
            $rData['errors']['user_roles'][0]);

    }

    /** @test */
    public function a_student_cannot_delete_his_own_account()
    {
        ActingAsHelper::getInstance()->reset();

//        $this->withoutExceptionHandling();
        $student = $this->getStudentOne();
        $this->assertTrue($student->isA('student'));

        $this->delete(
            route('user.destroy', ['user' => $student->uuid]),
            [
                'user'         => $student->username,
                'session_hash' => $student->session_hash,
            ]
        )->assertStatus(403);
        // let op een student kan nu dus wel zijn eigen account weggooien.

        $this->assertNotNull(User::find(1483));
    }

    /** @test */
    public function a_student_can_update_his_own_password()
    {
        $this->withoutExceptionHandling();
        $student = User::find(1483);
        $oldPassword = 'm.dehoogh@31.com';
        $student->setAttribute('password', \Hash::make($oldPassword));
        $student->save();
        $newPassword = 'm.dehoogh@31.comabc';

        $this->assertTrue(
            Hash::check($oldPassword, $student->password)
        );

        $this->assertFalse(
            Hash::check($newPassword, $student->password)
        );

        $this->put(
            route('user.update', [
                'user' => $student->uuid]),
            [
                'password_old'     => $oldPassword,
                'password'         => $newPassword,
                'password_confirm' => $newPassword,
                'user'             => $student->username,
                'session_hash'     => $student->session_hash,
            ]
        );

        $this->assertTrue(
            Hash::check($newPassword, $student->fresh()->password)
        );
    }

    /** @test */
    public function it_can_load_p_value_stats_by_subject_for_a_student()
    {
        $this->withoutExceptionHandling();

//        $factory = FactoryScenarioTestTakeRated::create($this->getTeacherOne());
        $studentOne = $this->getStudentOne();
        $this->assertEmpty($studentOne->pValueStatsForAllSubjects);
        $studentOne->loadPValueStatsForAllSubjects();
        dd($studentOne->pValueStatsForAllSubjects);
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

    /** @test */
    public function it_can_show_a_miller_p_value_report_for_a_subject()
    {
        $studentOne = $this->getStudentOne();
        $data = PValueRepository::getPValueForStudentForSubjectMiller(
            $studentOne,
            1,
            null,
            null,
            null

        );
        dd($data);

    }
}
