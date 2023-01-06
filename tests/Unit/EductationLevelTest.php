<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\Attainment;
use tcCore\EckidUser;
use tcCore\EducationLevel;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\TestCase;

class EductationLevelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_returns_the_latest_education_level_for_a_student()
    {
        $this->actingAs($this->getStudentOne());
        // subjectID 1 is nederlands;
        $educationLevel = EducationLevel::getLatestForStudentWithSubject($this->getStudentOne(), Subject::find(1));
        $this->assertInstanceOf(EducationLevel::class, $educationLevel);
        $this->assertEquals('VWO', $educationLevel->name);
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_called_with_a_teacher()
    {
        $this->expectException(\ErrorException::class);
        $this->expectDeprecationMessage('method can only be called as a student');
        EducationLevel::getLatestForStudentWithSubject($this->getTeacherOne(), Subject::find(1));
    }

    /** @test */
    public function it_throws_an_error_when_provided_subject_is_not_in_student_classes()
    {
        $this->expectException(\ErrorException::class);
        $this->expectDeprecationMessage('no school_class found for provided student and subject');
        EducationLevel::getLatestForStudentWithSubject($this->getStudentOne(), Subject::find(21));
    }

}
