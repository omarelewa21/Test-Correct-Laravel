<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 15:42
 */

namespace Tests;

use tcCore\Jobs\CountSchoolLocationStudents;
use tcCore\Role;
use tcCore\Text2Speech;
use tcCore\User;
use tcCore\UserRole;

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
            'school_location_id' => $manager->school_location_id,
        ]);

        factory(Text2Speech::class)->create([
            'user_id' => $student->getKey(),
            'active' => true
        ]);

        $return = $this->get($this->getUrlWithAuthCredentials('/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([])));

        $response = (object) $return->decodeResponseJson();
        $this->assertTrue($student->username == $response->username);
        $this->deleteUser($student);
    }

    /**
     * @test
     */
    public function it_should_update_text2speech_data()
    {
        $manager = User::where('username','=',static::USER_ACCOUNTMANAGER)->get()->first();

        $schoolLocation = $manager->schoolLocation;

        $refCount = User::where('text2speech','=',1)
            ->where('school_location_id','=',$schoolLocation->getKey())
            ->whereNull('deleted_at')
            ->count();

        $student = factory(User::class)->make([
            'text2speech' => false,
            'school_location_id' => $schoolLocation->getKey(),
            'school_id' => null
        ]);

        $student->fill([
            'user_roles' => 3
        ]);
        $student->save();

        // there should be no extra text2speech users
        $return = $this->get($this->getUrlWithAuthCredentials('/school_location/'.$schoolLocation->getKey(),static::getAuthRequestDataForAccountManager([])));

        $response = (object) $return->decodeResponseJson();

        $this->assertTrue($response->count_text2speech == $refCount);

        $return = $this->put('/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'text2speech' => true
        ]));

        $response = $return->decodeResponseJson();

        $student = $student->refresh();
        $this->assertTrue($student->hasText2Speech());
        $this->assertTrue($student->hasActiveText2Speech());
        $this->assertCount(1,$student->text2SpeechLog()->get());



        // there should be 1 extra text2speech user
        $return = $this->get($this->getUrlWithAuthCredentials('/school_location/'.$schoolLocation->getKey(),static::getAuthRequestDataForAccountManager([])));
        $response = (object) $return->decodeResponseJson();
        $newCount = $refCount+1;

//        echo 'RefCount: '.$refCount.PHP_EOL;
//        echo 'NewCount: '.$newCount.PHP_EOL;
//        echo 'Response: '.$response->count_text2speech.PHP_EOL;

        $this->assertTrue($response->count_text2speech == ($newCount));

        $return = $this->put('/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'active_text2speech' => false
        ]));

        $response = $return->decodeResponseJson();

        $student = $student->refresh();
        $this->assertTrue($student->hasText2Speech());
        $this->assertFalse($student->hasActiveText2Speech());
        $this->assertCount(2,$student->text2SpeechLog()->get());

        // even though there is no active text2speech user, there should still be an accepted text2speech user
        $return = $this->get($this->getUrlWithAuthCredentials('/school_location/'.$schoolLocation->getKey(),static::getAuthRequestDataForAccountManager([])));

        $response = (object) $return->decodeResponseJson();
//        echo 'RefCount: '.$refCount.PHP_EOL;
//        echo 'NewCount: '.$newCount.PHP_EOL;
//        echo 'Response: '.$response->count_text2speech.PHP_EOL;

        $this->assertTrue($response->count_text2speech == ($newCount));


        $return = $this->put('/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'active_text2speech' => true
        ]));

        $response = $return->decodeResponseJson();

        $student = $student->refresh();
        $this->assertTrue($student->hasText2Speech());
        $this->assertTrue($student->hasActiveText2Speech());
        $this->assertCount(3,$student->text2SpeechLog()->get());

        $this->deleteUser($student);
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

        $return = $this->put('/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([
            'text2speech' => 1,
        ]));

        $return = $this->get($this->getUrlWithAuthCredentials('/user/'.$student->getKey(),static::getAuthRequestDataForAccountManager([])));

        $response = (object) $return->decodeResponseJson();
        $this->assertTrue(isset($response->active_text2speech));

        $this->deleteUser($student);
    }

}