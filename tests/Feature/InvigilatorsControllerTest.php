<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Student;
use tcCore\User;
use Tests\TestCase;

class SchoolClassStudentImportControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_teacher_can_select_invigilators_and_see_that_teachers_are_added_when_a_new_teacher_is_associated_to_the_school_location_users()
    {
        $schoolLocationNumber4 = SchoolLocation::find(4);

        $teacherA = User::where('username', 'teacher-a@test-correct.nl')->first();
        $teacherB = User::where('username', 'teacher-b@test-correct.nl')->first();

        $this->assertEquals(5, $teacherA->school_location_id);

        $response = $this->get(
            self::authUserGetRequest(
                '/invigilator/list',
                [],
                $teacherA
            )
        );

        $this->assertCount(11, $response->decodeResponseJson());


        $teacherA->schoolLocation()->associate($schoolLocationNumber4);
        $teacherA->save();

        $this->assertEquals(4, $teacherA->school_location_id);

        $response = $this->get(
            self::authUserGetRequest(
                '/invigilator/list',
                [],
                $teacherA
            )
        );

        $this->assertCount(2, $response->decodeResponseJson());

        $teacherB->addSchoolLocation($schoolLocationNumber4);

        $response = $this->get(
            self::authUserGetRequest(
                '/invigilator/list',
                [],
                $teacherA
            )
        );

        $this->assertCount(3, $response->decodeResponseJson());
    }

}
