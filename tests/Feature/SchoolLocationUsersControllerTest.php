<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\TestCase;

class SchoolLocationUsersControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_user_can_be_added_to_an_extra_school_location()
    {
        $schoolLocationTwo = \tcCore\SchoolLocation::find(4);
        $teacherTwo = User::firstWhere('username','teacher-b@test-correct.nl' );
        $adminA = User::firstWhere('username','admin-a@test-correct.nl');

        $this->assertFalse($teacherTwo->isAllowedToSwitchToSchoolLocation($schoolLocationTwo));

        $response = $this->post(
            route(
                'school_location_user.store'
            ),
            static::getUserAuthRequestData($adminA, [
                'user_uuid' => $teacherTwo->uuid,
            ])
        );

        $response->assertSuccessful();
        $this->assertTrue($teacherTwo->isAllowedToSwitchToSchoolLocation($schoolLocationTwo));
    }

    /** @test */
    public function a_user_can_be_removed_from_an_extra_school_location()
    {
        $teacherOne = User::firstWhere('username', self::USER_TEACHER);

        $schoolLocationOne =  \tcCore\SchoolLocation::find(1);
        $schoolLocationTwo = \tcCore\SchoolLocation::find(2);

        $teacherOne
            ->addSchoolLocation($schoolLocationTwo);

        $this->assertTrue(
            $teacherOne->isAllowedToSwitchToSchoolLocation($schoolLocationTwo)
        );
        $this->assertEquals($teacherOne->schoolLocation,$schoolLocationOne);

        $response = $this->delete(
            route(
                'school_location_user.delete'
            ),
            static::getSchoolBeheerderAuthRequestData([
                'user_uuid' => $teacherOne->uuid,
                'school_location' => $schoolLocationTwo->uuid
            ])
        );

        $this->assertFalse(
            $teacherOne->isAllowedToSwitchToSchoolLocation($schoolLocationTwo)
        );

    }

    /** @test */
    public function when_a_user_is_removed_from_her_active_school_location_the_active_school_location_should_change()
    {
        $teacherOne = User::firstWhere('username', self::USER_TEACHER);

        $schoolLocationOne =  \tcCore\SchoolLocation::find(1);
        $schoolLocationTwo = \tcCore\SchoolLocation::find(2);

        $this->assertEquals(
            $schoolLocationOne,
            $teacherOne->schoolLocation
        );

        $teacherOne
            ->addSchoolLocation($schoolLocationTwo);

        $this->assertTrue(
            $teacherOne->isAllowedToSwitchToSchoolLocation($schoolLocationOne)
        );
        $this->assertEquals($teacherOne->schoolLocation,$schoolLocationOne);

        $this->delete(
            route(
                'school_location_user.delete'
            ),
            static::getSchoolBeheerderAuthRequestData([
                'user_uuid' => $teacherOne->uuid,
                'school_location' => $schoolLocationOne->uuid
            ])
        )->isSuccessful();

        $this->assertEquals(
            $schoolLocationTwo,
            $teacherOne->refresh()->schoolLocation
        );

    }

    /** @test */
    public function a_user_can_switch_his_active_school_location()
    {
        $teacherOne = User::firstWhere('username', self::USER_TEACHER);

        $schoolLocationOne =  \tcCore\SchoolLocation::find(1);
        $schoolLocationTwo = \tcCore\SchoolLocation::find(2);

        $teacherOne
            ->addSchoolLocation($schoolLocationTwo);

        $this->assertEquals($teacherOne->schoolLocation,$schoolLocationOne);


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
        $teacherOne = User::firstWhere('username', self::USER_TEACHER);

        $schoolLocationTwo = \tcCore\SchoolLocation::find(2);

        $teacherOne
            ->addSchoolLocation($schoolLocationTwo);


        $response = $this->get(static::authTeacherOneGetRequest(route('school_location_user.index'), []));

        $response->assertSuccessful();

        $this->assertCount(2, $response->decodeResponseJson());
        $first = $response->decodeResponseJson()[0];
        $this->assertEquals(1, $first['id']);
        $this->assertTrue( $first['active']);
        $this->assertFalse($response->decodeResponseJson()[1]['active']);

        $this->assertEquals('Open source schoolocatie1', $first['name']);
    }

    /** @test */
    public function when_a_teacher_with_single_access_is_granted_double_access_the_first_school_is_also_added()
    {
        $user = User::whereUsername('teacher-b@test-correct.nl')->first();
        $this->assertCount(
            0,
            DB::table('school_location_user')->where('user_id', $user->getKey())->get()
        );

        $user->addSchoolLocation(SchoolLocation::find(4));

        $this->assertCount(
            2,
            DB::table('school_location_user')->where('user_id', $user->getKey())->get()
        );
    }

    /** @test */
    public function when_a_admin()
    {


        $response = $this->get(
             self::authUserGetRequest(
                 route('school_location_user.get_existing_teachers'),
                 [],
                 User::firstWhere('username', 'admin-a@test-correct.nl')
             )
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function when_hitting_the_get_existing_teacher_endpoint_as_non_admin_i_get_four_o_three()
    {
        $response = $this->get(
            self::authUserGetRequest(
                route('school_location_user.get_existing_teachers'),
                [],
                User::firstWhere('username', 'teacher-a@test-correct.nl')
            )
        )->assertStatus(403);
    }
}
