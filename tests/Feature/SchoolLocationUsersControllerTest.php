<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\User;
use Tests\TestCase;

class SchoolLocationUsersControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_user_can_be_added_to_an_extra_school_location()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_user_can_be_removed_from_an_extra_school_location()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_user_can_switch_his_active_school_location()
    {
        $teacherOne = User::firstWhere('username', self::USER_TEACHER);

        $this->assertEquals($teacherOne->schoolLocation, \tcCore\SchoolLocation::find(1));

        $schoolLocationTwo = \tcCore\SchoolLocation::find(2);

        $response = $this->put(
            route(
                'school_location_user.update'
            ),
            static::getTeacherOneAuthRequestData([
                'school_location' => $schoolLocationTwo->uuid
            ])
        );
        $response->assertSuccessful();

        $this->assertEquals(
            $schoolLocationTwo,
            $teacherOne->refresh()->schoolLocation
        );
    }

    /** @test */
    public function a_user_cannot_switch_to_a_school_location_where_he_is_not_registered()
    {
        $teacherOne = User::firstWhere('username', self::USER_TEACHER);
        $schoolLocationOne = \tcCore\SchoolLocation::find(1);
        $this->assertEquals($teacherOne->schoolLocation, $schoolLocationOne);

        $schoolLocationThree = \tcCore\SchoolLocation::find(3);

        $response = $this->put(
            route(
                'school_location_user.update'
            ),
            static::getTeacherOneAuthRequestData([
                'school_location' => $schoolLocationThree->uuid
            ])
        );
        $response->assertStatus(403);

        $this->assertEquals(
            $schoolLocationOne,
            $teacherOne->refresh()->schoolLocation
        );
    }

    /** @test */
    public function a_user_can_request_a_list_of_school_locations()
    {
        $response = $this->get(static::authTeacherOneGetRequest(route('school_location_user.index'), []));

        $response->assertSuccessful();

        $this->assertCount(2, $response->decodeResponseJson());
        $first = $response->decodeResponseJson()[0];
        $this->assertEquals(1, $first['id']);
        $this->assertEquals('Open source schoolocatie1', $first['name']);

    }


}
