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
        $this->withoutExceptionHandling();

        $deVries = User::whereUsername(static::USER_TEACHER)->first();
        $oldPassword = $deVries->password;

        $response = $this->put(
            sprintf('/user/%d', $deVries->getKey()),
            static::getAuthRequestData([
                'password_old' => 'p.vries@31.com',
                'password'     => 'p.vries@31.com1',
                'password_new' => 'p.vries@31.com1',
            ])
        );

        $response->assertStatus(200);

        $deVries->refresh();
        $this->assertNotEquals(
            $deVries->password,
            $oldPassword
        );
    }
}
