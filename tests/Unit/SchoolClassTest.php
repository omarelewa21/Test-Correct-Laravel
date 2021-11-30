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
use tcCore\EckidUser;
use tcCore\Manager;
use tcCore\Mentor;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Student;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function when_deleting_a_school_class_relations_should_also_be_deleted()
    {
        $schoolClass = SchoolClass::find(1);
        $manager = new Manager(['user_id'=>1486]);
        $schoolClass->managers()->save($manager);
        $schoolClass->name = 'Klas 1 manager added';
        $schoolClass->save();
        $schoolClass->delete();
        $managers = Manager::withTrashed()->where('school_class_id',$schoolClass->getKey())->get();
        foreach ($managers as $manager){
            $manager->fresh();
            $this->assertNotNull($manager->deleted_at);
        }
        $mentors = Mentor::withTrashed()->where('school_class_id',$schoolClass->getKey())->get();
        foreach ($mentors as $mentor){
            $mentor->fresh();
            $this->assertNotNull($mentor->deleted_at);
        }
        $teachers = Teacher::withTrashed()->where('class_id',$schoolClass->getKey())->get();
        foreach ($teachers as $teacher){
            $teacher->fresh();
            $this->assertNotNull($teacher->deleted_at);
        }
        $students = Student::withTrashed()->where('class_id',$schoolClass->getKey())->get();
        foreach ($students as $student){
            $student->fresh();
            $this->assertNotNull($student->deleted_at);
        }
    }
}
