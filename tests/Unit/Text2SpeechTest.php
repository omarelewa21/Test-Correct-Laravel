<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */
namespace Tests\Unit;

use tcCore\Text2speech;
use tcCore\Text2speechLog;
use tcCore\User;
use Tests\TestCase;

class Text2SpeechTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /**
     * @test
     */
    public function it_should_show_text2speech_for_the_user()
    {
        $user = factory(User::class)->create([
            'text2speech' => true
        ]);

        $this->assertTrue($user->hasText2Speech());
        $this->deleteUser($user);
    }

    /**
     * @test
     */
    public function it_should_show_active_text2speech_for_the_user()
    {
        $user = factory(User::class)->create([
            'text2speech' => true
        ]);

        factory(Text2speech::class)->create([
            'user_id' => $user->getKey(),
            'active' => true
        ]);

        $this->assertTrue($user->hasActiveText2Speech());
        $this->deleteUser($user);
    }

    /**
     * @test
     */
    public function it_should_show_inactive_text2speech_for_a_text2speech_user()
    {
        $user = factory(User::class)->create([
            'text2speech' => true
        ]);

        factory(Text2speech::class)->create([
            'user_id' => $user->getKey(),
            'active' => false
        ]);

        $this->assertTrue($user->hasText2Speech());

        $this->assertFalse($user->hasActiveText2Speech());

        $this->deleteUser($user);
    }

    /**
     * @test
     */
    public function it_should_show_a_log_record_for_a_text2speech_user()
    {
        $user = factory(User::class)->create([
            'text2speech' => true
        ]);

        factory(Text2speech::class)->create([
            'user_id' => $user->getKey(),
            'active' => false
        ]);

        factory(Text2speechLog::class)->create([
            'user_id'    => $user->getKey(),
            'action'     => 'ACCEPTED',
        ]);

        $this->assertTrue($user->hasText2Speech());

        $this->assertFalse($user->hasActiveText2Speech());

        $this->assertCount(1, $user->text2SpeechLog()->get());

        $this->deleteUser($user);
    }
}