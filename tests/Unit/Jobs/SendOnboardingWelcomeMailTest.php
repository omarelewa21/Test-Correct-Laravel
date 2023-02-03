<?php

namespace Tests\Unit\Jobs;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Jobs\SendNotifyInviterMail;
use tcCore\Jobs\SendOnboardingWelcomeMail;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ScenarioLoader;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class SendOnboardingWelcomeMailTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;


    private User $teacherOne;

    private User $teacherTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('user');
        $this->teacherTwo = FactoryUser::createTeacher($this->teacherOne->schoolLocation)->user;
        ActingAsHelper::getInstance()->setUser($this->teacherOne);
    }


    /** @test */
    public function send_onboarding_welcome_mail_should_not_break_on_render()
    {
        $mailable = new SendOnboardingWelcomeMail($this->teacherOne,'');
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
        $mailable = new SendOnboardingWelcomeMail($this->teacherTwo,'');
        $this->teacherTwo->delete();
        $this->assertFalse($mailable->render());
    }
}
