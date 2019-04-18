<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 15:42
 */

namespace Tests;


use tcCore\Text2speech;
use tcCore\User;
use TestCase;

class Text2SpeechTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /**
     * @test
     */
    public function it_should_return_user_data()
    {
        $manager = User::where('username','=',static::USER_ACCOUNTMANAGER)->get()->first();
        $student = factory(User::class)->create([
            'text2speech' => true,
            'school_location_id' => $manager->school_location_id
        ]);

        factory(Text2speech::class)->create([
            'user_id' => $student->getKey(),
            'active' => true
        ]);

        $return = $this->makeREquest('GET','/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
        ]));

        $response = json_decode($this->response->getContent());
        $this->assertTrue($student->username == $response->username);
    }

    /**
     * @test
     */
    public function it_should_update_text2speech_data()
    {
        $manager = User::where('username','=',static::USER_ACCOUNTMANAGER)->get()->first();
        $student = factory(User::class)->create([
            'text2speech' => false,
            'school_location_id' => $manager->school_location_id
        ]);
        $return = $this->makeREquest('PUT','/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'text2speech' => true
        ]));

        $response = json_decode($this->response->getContent());

        $student = $student->fresh();
        $this->assertTrue($student->hasText2Speech());
        $this->assertTrue($student->hasActiveText2Speech());
        $this->assertCount(1,$student->text2SpeechLog()->get());

        $return = $this->makeREquest('PUT','/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'active_text2speech' => false
        ]));

        $response = json_decode($this->response->getContent());

        $student = $student->fresh();
        $this->assertTrue($student->hasText2Speech());
        $this->assertFalse($student->hasActiveText2Speech());
        $this->assertCount(2,$student->text2SpeechLog()->get());

        $return = $this->makeREquest('PUT','/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'active_text2speech' => true
        ]));

        $response = json_decode($this->response->getContent());

        $student = $student->fresh();
        $this->assertTrue($student->hasText2Speech());
        $this->assertTrue($student->hasActiveText2Speech());
        $this->assertCount(3,$student->text2SpeechLog()->get());
    }

    /**
     * @test
     */
    public function it_should_have_active_text2speech_as_attribute()
    {
        $manager = User::where('username','=',static::USER_ACCOUNTMANAGER)->get()->first();
        $student = factory(User::class)->create([
            'text2speech' => false,
            'school_location_id' => $manager->school_location_id
        ]);

        $return = $this->makeREquest('PUT','/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'text2speech' => 1,
        ]));

        $return = $this->makeREquest('GET','/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([

        ]));

        $response = json_decode($this->response->getContent());
        $this->assertTrue(isset($response->active_text2speech));
    }

}