<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\BaseSubject;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSection;
use tcCore\Section;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;

class SharedSectionsTestTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

   /** @test */
   public function a_teacher_should_get_shared_tests()
   {
       $teacherOne = User::whereUsername(self::USER_TEACHER)->first();
       Auth::login($teacherOne);

       $this->assertEquals(0,Test::SharedSectionsFiltered()->count());

       $data = $this->addCustomSectionsAndSubjectsAndCreateTestWithCustomSubjectB();

       $teacher = $teacherOne->teacher()->first();
       Teacher::create([
          'class_id' => $teacher->class_id,
          'subject_id'=> $data->customSubjectA->getKey(),
          'user_id' => $teacherOne->getKey()
       ]);

       SchoolLocationSection::create([
          'school_location_id' => $teacherOne->school_location_id,
          'section_id' => $data->customSectionA->getKey()
       ]);

       $this->shareSectionToSchoollocation($data->customSectionB,$teacherOne->schoolLocation);

       $this->assertEquals(1,Test::SharedSectionsFiltered()->count());
   }

    /** @test */
    public function a_teacher_should_not_get_shared_tests_if_base_subject_not_in_shared_section()
    {
        $teacherOne = User::whereUsername(self::USER_TEACHER)->first();
        Auth::login($teacherOne);

        $data = $this->addCustomSectionsAndSubjectsAndCreateTestWithCustomSubjectA();

        $this->shareSectionToSchoollocation($data->customSectionA,$teacherOne->schoolLocation);

        $teacherTwo = User::whereUsername(self::USER_TEACHER2)->first();
        Auth::login($teacherTwo);

        $this->assertEquals(0,Test::SharedSectionsFiltered()->count());

    }




    protected function shareSectionToSchoolLocation(Section $section,SchoolLocation $schoolLocation)
    {
        ($schoolLocation->sharedSections()->attach($section->getKey()));
    }

   protected function addCustomSectionsAndSubjectsAndCreateTestWithCustomSubjectB()
   {
       $customSectionA = Section::create([
           'name' => 'customA',
           'demo' => false,
       ]);

       $customBaseSubject = BaseSubject::create([
           'name' => 'custom base subject',
       ]);

       $customSubjectA = Subject::create([
           'section_id' => $customSectionA->getKey(),
           'base_subject_id' => $customBaseSubject->getKey(),
           'name' => 'custom subject a',
           'abbreviation' => 'csa',
       ]);

       $customSectionB = Section::create([
           'name' => 'customB',
           'demo' => false,
       ]);

       $customSubjectB = Subject::create([
           'section_id' => $customSectionB->getKey(),
           'base_subject_id' => $customBaseSubject->getKey(),
           'name' => 'custom subject b',
           'abbreviation' => 'csb',
       ]);

       // create a test which should become visible for the teacher
       $test = Test::first();
       $newTest = $test->duplicate(['subject_id' => $customSubjectB->getKey()]);

       return (object) [
         'customSectionA' => $customSectionA,
         'customSubjectA' => $customSubjectA,
         'customSectionB' => $customSectionB,
         'customSubjectB' => $customSubjectB,
         'test' => $newTest
       ];

   }

}
