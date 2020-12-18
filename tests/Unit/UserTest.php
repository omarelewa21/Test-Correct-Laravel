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

}
