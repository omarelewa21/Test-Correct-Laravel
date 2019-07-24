<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_should_change_the_password()
    {
        $teacher1 = User::whereUsername('d1@test-correct.nl')->first();
        $oldPassword = $teacher1->password;

        $response = $this->put(
            sprintf('/user/%d', $teacher1->getKey()),
            static::getTeacherOneAuthRequestData([
                'password_old' => 'Sobit4456',
                'password'     => 'p.vries@31.com1',
                'password_new' => 'p.vries@31.com1',
            ])
        );

        $response->assertStatus(200);

        $teacher1->refresh();
        $this->assertNotEquals(
            $teacher1->password,
            $oldPassword
        );
    }
}
