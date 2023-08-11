<?php

namespace Tests\Unit\Jobs;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ScenarioLoader;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class SendWelcomeMailTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimpleWithTest::class;

    private User $teacherOne;
    private User $studentOne;


    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherOne = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
    }


    /** @test */
    public function it_should_send_a_email_for_a_student()
    {
        dispatch_sync($mailable = new SendWelcomeMail($this->studentOne->getKey(), ''));

        $this->assertStringContainsString('Beste Student,', $mailable->testBody->getBody());
        $this->assertStringNotContainsString('Wachtwoord', $mailable->testBody->getBody());
    }

    /** @test */
    public function it_should_send_a_email_for_a_teacher()
    {
        dispatch_sync($mailable = new SendWelcomeMail($this->teacherOne->getKey(), ''));

        $this->assertStringContainsString(
            sprintf('Hallo %s!', $this->teacherOne->name_first),
            $mailable->testBody->getBody()
        );
        $this->assertStringNotContainsString('Wachtwoord', $mailable->testBody->getBody());
    }
}
