<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\SchoolLocation;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function when_deleting_a_teacher_that_is_a_member_of_two_school_location_the_teacher_gets_removed_the_memberships_table_not_deleted()
    {
        $adminA = User::whereUsername('admin-a@test-correct.nl')->first();
        $this->actingAs($adminA);

        $teacherA = User::whereUsername('teacher-a@test-correct.nl')->first();

        $this->assertTrue(
            $teacherA->isAllowedToSwitchToSchoolLocation($adminA->schoolLocation)
        );

        $teacherA->delete();

        $this->assertFalse(
            $teacherA->isAllowedToSwitchToSchoolLocation($adminA->schoolLocation)
        );

        $this->assertNotNull($teacherA->refresh());

        $teacherA->delete();
        $this->assertNull(User::whereUsername('teacher-a@test-correct.nl')->first());
    }

    /** @test */
    public function after_create_a_teacher_has_a_school_location()
    {
        $data =[
            'school_location_id' => '2',
            'name_first' => 'a',
            'name_suffix' => '',
            'name' => 'bc',
            'abbreviation' => 'abcc',
            'username' => 'abc@test-correct.nl',
            'password' => 'aa',
            'external_id' => 'abc',
            'note' => '',
            'user_roles' => [1],
        ];

        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        //dump($response->getContent());
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $this->assertTrue($rData['school_location']['id']==2);
    }

    /** @test */
    public function after_update_a_teacher_has_a_school_location_different_external_id_in_school_location_user()
    {
        $data =[
            'school_location_id' => '2',
            'name_first' => 'a',
            'name_suffix' => '',
            'name' => 'bc',
            'abbreviation' => 'abcc',
            'username' => 'abc@test-correct.nl',
            'password' => 'aa',
            'external_id' => 'abc',
            'note' => '',
            'user_roles' => [1],
        ];

        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        //dump($response->getContent());
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $user = User::find($rData['id']);
        $this->assertTrue($rData['school_location']['id']==2);
        $schoolLocations = $user->schoolLocations()->get();
        foreach ($schoolLocations as $schoolLocation){
            $this->assertEquals('abc',$schoolLocation->pivot->external_id);
            $this->assertEquals(2,$schoolLocation->pivot->school_location_id);
            //dump($schoolLocation->pivot->external_id);
        }
        $data['id'] = $rData['id'];
        $data['uuid'] = $rData['uuid'];
        $data['external_id'] = 'cde';
        $response = $this->put(
            'api-c/user/'.$rData['uuid'],
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $schoolLocations = $user->schoolLocations()->get();
        foreach ($schoolLocations as $schoolLocation){
            $this->assertEquals('cde',$schoolLocation->pivot->external_id);
            //dump($schoolLocation->pivot->external_id);
        }
    }

    /** @test */
    public function create_and_update_teacher_fails_when_external_already_exists_in_school_location()
    {
        $data =[
            'school_location_id' => '2',
            'name_first' => 'a',
            'name_suffix' => '',
            'name' => 'bc',
            'abbreviation' => 'abcc',
            'username' => 'abc@test-correct.nl',
            'password' => 'aa',
            'external_id' => 'abc',
            'note' => '',
            'user_roles' => [1],
        ];

        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        //dump($response->getContent());
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $data['username'] = 'cde@test-correct.nl';
        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(422);
        $data['external_id'] = 'cde';
        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(200);
        $data['username'] = 'abc@test-correct.nl';
        $response = $this->put(
            'api-c/user/'.$rData['uuid'],
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(422);
        $data['external_id'] = 'efg';
        $response = $this->put(
            'api-c/user/'.$rData['uuid'],
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(200);
    }
}
