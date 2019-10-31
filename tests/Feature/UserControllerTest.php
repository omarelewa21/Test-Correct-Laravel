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
