<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_student_cannot_delete_his_own_account()
    {
//        $this->disableExceptionHandling();
        $student = User::find(1483);
        $this->assertTrue($student->hasRole('student'));

//        dd(route('user.destroy'));


        $this->delete(
            route('user.destroy', ['user' => $student->getKey()]),
            [
                'user' => $student->username,
                'session_hash' => $student->session_hash,
            ]
        )->assertStatus(403);
        // let op een student kan nu dus wel zijn eigen account weggooien.

        $this->assertNotNull(User::find(1483));
    }

    /** @test */
    public function a_student_can_update_his_own_password()
    {
        $this->disableExceptionHandling();
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
                'user' => $student->getKey()]),
            [
                'password_old' => $oldPassword,
                'password' => $newPassword,
                'password_confirm' => $newPassword,
                'user' => $student->username,
                'session_hash' => $student->session_hash,
            ]
        );

        $this->assertTrue(
            Hash::check($newPassword, $student->fresh()->password)
        );
    }

    /** @test */
    public function a_teacher_cannot_be_added_if_no_current_active_period()
    {
//        $this->disableExceptionHandling();
        \tcCore\SchoolLocationSchoolYear::where('school_location_id',2)->orderby('created_at','desc')->first()->delete();

        $data =[
            'school_location_id' => '2',
            'name_first' => 'a',
            'name_suffix' => '',
            'name' => 'bc',
            'abbreviation' => 'abcc',
            'username' => 'abc@test-correct.nl',
            'password' => 'aa',
            'external_id' => 'abc',
            'note' => '',
            'user_roles' => [1],
        ];

        $response = $this->post(
            '/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(422);
        $rData = $response->decodeResponseJson();
        $this->assertEquals('U kunt een docent pas aanmaken nadat u een actuele periode heeft aangemaakt. Dit doet u door als schoolbeheerder in het menu Database -> Schooljaren een schooljaar aan te maken met een periode die in de huidige periode valt.',
            $rData['errors']['user_roles'][0]);

    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler
        {
            public function __construct()
            {
            }

            public function report(Exception $e)
            {
                // no-op
            }

            public function render($request, Exception $e)
            {
                throw $e;
            }
        });
    }
}
