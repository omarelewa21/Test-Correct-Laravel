<?php

namespace Tests\Unit\Jobs;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class SendWelcomeMailTest extends TestCase
{
//    use DatabaseTransactions;

    /** @test */
    public function it_should_send_a_email_for_a_student()
    {
        $user = User::find(1484);
        dispatch_now($mailable = new SendWelcomeMail($user->getKey(), ''));

        $this->assertStringContainsString('Beste student,', $mailable->testBody);
        $this->assertStringNotContainsString('Wachtwoord', $mailable->testBody);
    }

    /** @test */
    public function it_should_send_a_email_for_a_teacher()
    {
        $user = User::find(1486);
        dispatch_now($mailable = new SendWelcomeMail($user->getKey(), ''));

        $this->assertStringContainsString('Hallo Docent!', $mailable->testBody);
        $this->assertStringNotContainsString('Wachtwoord', $mailable->testBody);
    }
}
