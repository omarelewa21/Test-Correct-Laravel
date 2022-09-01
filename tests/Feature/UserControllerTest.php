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


}
