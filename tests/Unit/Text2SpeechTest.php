<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */
namespace Tests\Unit;

use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Text2Speech;
use tcCore\Text2SpeechLog;
use Tests\ScenarioLoader;
use Tests\TestCase;

class Text2SpeechTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_should_show_text2speech_for_the_user()
    {
        $user = $this->createStudentWithText2Speech();

        $this->assertTrue($user->hasText2Speech());
    }

    /**
     * @test
     */
    public function it_should_show_active_text2speech_for_the_user()
    {
        $user = $this->createStudentWithText2Speech();

        factory(Text2Speech::class)->create([
            'user_id' => $user->getKey(),
            'active' => true
        ]);

        $this->assertTrue($user->hasActiveText2Speech());
    }

    /**
     * @test
     */
    public function it_should_show_inactive_text2speech_for_a_text2speech_user()
    {
        $user = $this->createStudentWithText2Speech();

        factory(Text2Speech::class)->create([
            'user_id' => $user->getKey(),
            'active' => false
        ]);

        $this->assertTrue($user->hasText2Speech());

        $this->assertFalse($user->hasActiveText2Speech());
    }

    /**
     * @test
     */
    public function it_should_show_a_log_record_for_a_text2speech_user()
    {
        $user = $this->createStudentWithText2Speech();

        factory(Text2Speech::class)->create([
            'user_id' => $user->getKey(),
            'active' => false
        ]);

        factory(Text2SpeechLog::class)->create([
            'user_id'    => $user->getKey(),
            'action'     => 'ACCEPTED',
        ]);

        $this->assertTrue($user->hasText2Speech());

        $this->assertFalse($user->hasActiveText2Speech());

        $this->assertCount(1, $user->text2SpeechLog()->get());
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function createStudentWithText2Speech()
    {
        return FactoryUser::createStudent(
            ScenarioLoader::get('school_locations')->first(),
            ['text2speech' => true]
        )->user;
    }
}