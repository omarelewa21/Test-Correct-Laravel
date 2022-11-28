<?php

namespace Tests\Unit\Jobs;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\SendNotifyInviterMail;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class SendNotifyInviterMailTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function send_notify_inviter_mail_should_not_break_on_render()
    {
        $inviter = User::where('username',self::USER_TEACHER)->first();
        $invitee = User::where('username',self::USER_TEACHER_TWO)->first();
        $mailable = new SendNotifyInviterMail($inviter, $invitee);
        $pass = true;
        try{
            $mailable->render();
        }catch(\Exception $e){
            $pass = false;
        }
        $this->assertTrue($pass);
    }

    /** @test */
    public function send_notify_inviter_mail_should_not_render_after_user_delete()
    {
        $inviter = User::where('username',self::USER_TEACHER)->first();
        $invitee = User::where('username',self::USER_TEACHER_TWO)->first();
        $mailable = new SendNotifyInviterMail($inviter, $invitee);
        $inviter->delete();
        $this->assertFalse($mailable->render());
    }

}
