<?php

namespace Tests\Unit\Jobs;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\SendNotifyInviterMail;
use tcCore\Jobs\SendOnboardingWelcomeMail;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class SendOnboardingWelcomeMailTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function send_onboarding_welcome_mail_should_not_break_on_render()
    {
        $invitee = User::where('username',self::USER_TEACHER_TWO)->first();
        $mailable = new SendOnboardingWelcomeMail($invitee,'');
        $pass = true;
        try{
            $mailable->render();
        }catch(\Exception $e){
            $pass = false;
        }
        $this->assertTrue($pass);
    }

    /** @test */
    public function send_onboarding_welcome_mail_should_not_render_after_user_delete()
    {
        $invitee = User::where('username',self::USER_TEACHER_TWO)->first();
        $mailable = new SendOnboardingWelcomeMail($invitee,'');
        $invitee->delete();
        $this->assertFalse($mailable->render());
    }

}
