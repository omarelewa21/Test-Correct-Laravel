<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use tcCore\Answer;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Factory;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationEducationLevel;
use tcCore\SchoolLocationSection;
use tcCore\SchoolYear;
use tcCore\Section;
use tcCore\Student;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\Text2Speech;
use tcCore\Text2SpeechLog;
use tcCore\User;

class DemoHelper
{
    protected $schoolLocation;
    protected $section;
    protected $subject;
    const SUBJECTNAME = 'Demovak';
    const SECTIONNAME = 'Demo';
    const CLASSNAME = 'Demoklas';
    const EDUCATIONLEVELNAME = 'Demo';
    const TESTNAME = 'Test-Correct demotoets %s %s %s';
    const BASEDEMOTESTNAME = 'TC-DEMO-BASE';
    const TESTABBR = 'DEMO';
    const TEACHERLASTNAMEBASE = 'TLC demodocent';
    const SCHOOLLOCATIONNAME = 'DEMO TOUR SCHOOL 01';

    public function setSchoolLocation(SchoolLocation $schoolLocation)
    {
        $this->schoolLocation = $schoolLocation;
        return $this;
    }

    public function setSection(Section $section)
    {
        $this->section = $section;
        return $this;
    }

    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getDemoSubject()
    {
        if ($this->schoolLocation === null) {
            throw new \Exception('schoollocation not set');
        }
        $section = $this->getDemoSection();
        if ($section !== null) {
            return $this->getDemoSubjectIfExists($section);
        }
        return null;
    }

    public function getDemoSubjectForTeacher(User $user)
    {
        $this->setSchoolLocation($user->schoolLocation);
        $section = $this->getDemoSection();
        if ($section !== null) {
            return $this->getDemoSubjectIfExists($section);
        }
        return null;
    }

    public function getDemoSectionForSchoolLocation($schoolLocationId)
    {
        $section = Section::join('school_location_sections',function ($join) {
                                        $join->on('sections.id', '=', 'school_location_sections.section_id');
                                 })->where('school_location_sections.school_location_id',$schoolLocationId)
                                ->where('sections.name', self::SECTIONNAME)
                                ->first();
        if ($section !== null) {
            return $this->getDemoSubjectIfExists($section);
        }
        return null;
    }



    public function hasTeacherDemoSetup(User $user)
    {
        $this->setSchoolLocation($user->schoolLocation);
        $schoolClass = $this->getDemoClass();
        if ($schoolClass === null || Teacher::where('user_id', $user->getKey())->where('class_id', $schoolClass->getKey())->count() < 1) {
            return false;
        }
        if (Test::where('name', $this->getTestNameForTeacher(null, $user))->count() < 1) {
            return false;
        }
        if (Teacher::where('user_id', $user->getKey())->where('class_id', $schoolClass->getKey())->first() == null) {
            return false;
        }

        self::moveSchoolLocationDemoClassToCurrentYearIfNeeded($user->schoolLocation, $schoolClass);

        return true;
    }

    public static function moveSchoolLocationDemoClassToCurrentYearIfNeeded(SchoolLocation $schoolLocation, $schoolClass = null)
    {
        $helper = new DemoHelper();

        if($schoolClass == null){
            $helper->setSchoolLocation($schoolLocation);
            $schoolClass = $helper->getDemoClass();
        }
        if($schoolClass) {
            $schoolYear = SchoolYearRepository::getCurrentSchoolYear();
            if ($schoolYear != null) {
                if ($schoolClass->schoolYear != $schoolYear) {
                    $helper->moveDemoClassToNewSchoolYear($schoolClass, $schoolYear);
                }
            }
        }
    }

    public function createDemoForTeacherIfNeeded(User $user)
    {
        if (!$this->hasTeacherDemoSetup($user)) {
            $this->createDemoForSchoolLocationIfNeeded($user->schoolLocation);
            $schoolYear = SchoolYearRepository::getCurrentSchoolYear();
            if ($schoolYear !== null) {
                $this->prepareDemoForNewTeacher($user->schoolLocation, $schoolYear, $user);
            }
        }
    }

    public function hasSchoolDemoSetup(SchoolLocation $schoolLocation)
    {
        $this->setSchoolLocation($schoolLocation);
        return User::where('username', $this->getUsername('teacher'))->first() !== null
            && $this->getDemoEducationLevel()
            && $this->getDemoClass();
    }

    public function createDemoForSchoolLocationIfNeeded(SchoolLocation $schoolLocation)
    {
        if(GlobalStateHelper::getInstance()->hasPreventDemoEnvironmentCreationForSchoolLocation() === false) {
            if (!$this->hasSchoolDemoSetup($schoolLocation)) {
                $this->createDemoPartsForSchool($schoolLocation);
            }
        }
    }

    public function createDemoPartsForSchool(SchoolLocation $schoolLocation)
    {
        $this->setSchoolLocation($schoolLocation);
        // create demo section
        $this->createDemoSectionIfNeeded();
        // create demo subject (vak) if not existent
        $this->createDemoSubjectIfNeeded();
        // create demo teacher

        $this->createDemoTeacherIfNeeded();
        // create demo students
        $this->createDemoStudentsIfNeeded();
        // add education level to school
        $this->addDemoEducationlevelToSchoolIfNeeded();
    }

    public function prepareDemoForNewTeacher(SchoolLocation $schoolLocation, SchoolYear $schoolYear, User $user)
    {
        $currentQueueState = GlobalStateHelper::getInstance()->isQueueAllowed();
        GlobalStateHelper::getInstance()->setQueueAllowed(false);
        $this->setSchoolLocation($schoolLocation);
        $schoolClass = $this->createDemoClassForSchoolLocationAndPopulate($schoolLocation, $schoolYear);
        $teacher = $this->addTeacherToDemoClassIfNeeded($user, $schoolClass);
        $students = $this->createDemoStudentsIfNeeded();
        $baseDemoTest = $this->getBaseDemoTest();
        $returnData = $this->createDemoTestsForTeacher($teacher, $students, $baseDemoTest, $schoolClass);
        $returnData['teacher'] = $teacher;
        GlobalStateHelper::getInstance()->setQueueAllowed($currentQueueState);
        return $returnData;
    }

    public function createDemoClassForSchoolLocationAndPopulate(SchoolLocation $schoolLocation, SchoolYear $schoolYear)
    {
        $this->setSchoolLocation($schoolLocation);
        $schoolClass = $this->getDemoClass();
        if (!$schoolClass) {
            $schoolClass = $this->createDemoClassIfNeeded($schoolYear);
        } else if ($schoolClass->schoolYear !== $schoolYear) {
            $schoolClass = $this->moveDemoClassToNewSchoolYear($schoolClass, $schoolYear);
        }

        $teacher = $this->createDemoTeacherIfNeeded();
        $students = $this->createDemoStudentsIfNeeded();

        $this->addTeacherToDemoClassIfNeeded($teacher, $schoolClass);

        $students->each(function (User $s) use ($schoolClass) {
            $attr = [
                'user_id' => $s->getKey(),
                'class_id' => $schoolClass->getKey()
            ];
            $student = Student::withTrashed()->firstOrCreate($attr);
            if($student->trashed()) {
                $student->restore();
            }

        });
        return $schoolClass;
    }

    public function changeDemoUsersAsSchoolLocationCustomerCodeChanged(SchoolLocation $schoolLocation, $oldCustomerCode)
    {
        $this->setSchoolLocation($schoolLocation);

        $userDetails = collect([
            ['nr' => '01'],
            ['nr' => '02'],
            ['nr' => '03'],
            ['nr' => '04'],
            ['nr' => '05'],
        ]);
        $userDetails->each(function ($u) use ($oldCustomerCode) {
            $u = (object)$u;
            $user = User::where('school_location_id', $this->schoolLocation->getKey())
                ->where('username', $this->getUsername('student', $u->nr, $oldCustomerCode))->first();
            if ($user) {
                $user->username = $this->getUsername('student', $u->nr);
                $user->demoRestrictionOverrule = true;
                $user->save();
            }
        });

        $user = User::where('school_location_id', $this->schoolLocation->getKey())
            ->where('username', $this->getUsername('teacher', null, $oldCustomerCode))->first();
        if ($user) {
            $user->username = $this->getUsername('teacher');
            $user->name = sprintf('%s %s', self::TEACHERLASTNAMEBASE, $this->schoolLocation->customer_code);
            $user->demoRestrictionOverrule = true;
            $user->save();
        }
    }

    public function getDemoClass()
    {
        return SchoolClass::where('school_location_id', $this->schoolLocation->getKey())
            ->where('name', self::CLASSNAME)
            ->first();
    }

    protected function addDemoEducationlevelToSchoolIfNeeded()
    {
        $attr = [
            'school_location_id' => $this->schoolLocation->getKey(),
            'education_level_id' => $this->getDemoEducationLevel()->getKey()
        ];
        $a = SchoolLocationEducationLevel::withTrashed()->firstOrCreate($attr);
        if($a->trashed()){
            $a->restore();
        }
        return $a;
    }

    /** create demo tests and testtakes */

    protected function createDemoTestForTeacher(Teacher $teacher, Test $demoTest)
    {
        $attributes = [
            'name' => $this->getTestNameForTeacher($teacher),
            'subject_id' => $teacher->subject->getKey(),
//            'period_id' => PeriodRepository::getCurrentPeriod()->getKey(),
            'abbreviation' => self::TESTABBR,
            'education_level_year' => 1,
            'demo' => true,
            'author_id' => $teacher->user->getKey()
        ];

        $testQuery = Test::whereNull('deleted_at');
        foreach ($attributes as $key => $val) {
            $testQuery->where($key, $val);
        }
        $returnData = [
            'new' => false,
        ];
        $test = $testQuery->first(); // is there a test with all these specs? if not create one
        $periodId = PeriodRepository::getCurrentPeriod()->getKey();
        if (!$test) {
            $returnData['new'] = true;
            $test = $demoTest->duplicate(
                array_merge(
                    $attributes,
                    [
                        'period_id' => $periodId,
                        'education_level_id' => $this->getDemoEducationLevel()->getKey()
                    ]
                ),
                $teacher->user->getKey()
            );
            $test->is_system_test = 0;
            $test->save();
        } else {
            $test->period_id = $periodId;
            $test->save();
        }

        $returnData['test'] = $test;
        return $returnData;
    }

    protected function getBaseDemoTest()
    {

        $test = Test::where('name', self::BASEDEMOTESTNAME)->orderBy('created_at', 'desc')->get()->first(function (Test $t) {
            return TestTake::where('test_id', $t->getKey())->where('test_take_status_id', 9)->count() > 0;
        });


        if ($test === null) {
            throw new \Exception('base demo test needs to be created with title ' . self::BASEDEMOTESTNAME);
        }
        return $test;
    }

    protected function createDemoTestsForTeacher(Teacher $teacher, $students, Test $baseDemoTest, SchoolClass $schoolClass)
    {
        $data = $this->createDemoTestForTeacher($teacher, $baseDemoTest);

        $periodId = PeriodRepository::getCurrentPeriod()->getKey();

        $testTakes = TestTake::where('test_id', $baseDemoTest->getKey())->get()->transform(function (TestTake $testTake) use ($teacher, $students, $schoolClass, $periodId, $data) {
            $helper = new TestTakeDuplicateHelper();
            $data = $helper->collect($testTake->getKey(), $data['test']->getKey(), $teacher->user->getKey(), $students->pluck('id'), $schoolClass->getKey(), $periodId, $this->schoolLocation->getKey())->duplicate()->getNew();
            return $data['testTake'];
        });

        return [
            'new' => $data['new'],
            'test' => $data['test'],
            'testTakes' => $testTakes,
        ];
    }


    /** create demo class */

    protected function createDemoClassIfNeeded(SchoolYear $schoolYear)
    {
        $attr = [
            'school_location_id' => $this->schoolLocation->getKey(),
            'education_level_id' => $this->getDemoEducationLevel()->getKey(),
            'school_year_id' => $schoolYear->getKey(),
            'name' => self::CLASSNAME,
            'education_level_year' => 1,
            'is_main_school_class' => 0,
            'do_not_overwrite_from_interface' => 1,
            'demo' => 1,
        ];

        $return = SchoolClass::withTrashed()->firstOrCreate($attr);
        if($return->trashed()){
            $return->restore();
        }
        return $return;
    }

    protected function moveDemoClassToNewSchoolYear(SchoolClass $schoolClass, SchoolYear $schoolYear)
    {
        $schoolClass->fill([
            'demo_restriction_overrule' => true,
            'school_year_id' => $schoolYear->getKey()
        ]);
        $schoolClass->save();
        return $schoolClass;
    }


    /** teacher data */
    protected function addTeacherToDemoClassIfNeeded(User $user, SchoolClass $schoolClass)
    {
        $subject = $this->createDemoSubjectIfNeeded();

        $return = Teacher::withTrashed()->firstOrCreate([
            'user_id' => $user->getKey(),
            'class_id' => $schoolClass->getKey(),
            'subject_id' => $subject->getKey()
        ]);
        if($return->trashed()){
            $return->restore();
        }
        return $return;
    }

    protected function getTestNameForTeacher(Teacher $teacher = null, User $user = null)
    {
        if ($teacher === null && $user === null) {
            throw new \Exception('There needs to be a teacher or user to get the name of the test');
        }
        if ($user === null) {
            $user = $teacher->user;
        }
        return str_replace('  ', ' ', sprintf(self::TESTNAME, $user->name_first, $user->name_suffix, $user->name));
    }

    protected function createDemoTeacherIfNeeded()
    {
        $user = User::where('username', $this->getUsername('teacher'))->first();
        if (null === $user) {
            $userFactory = new Factory(new User());

            $user = $userFactory->generate([
                'name_first' => ' ',
                'name' => sprintf('%s %s', self::TEACHERLASTNAMEBASE, $this->schoolLocation->customer_code),
                'abbreviation' => 'DD01',
                'username' => $this->getUsername('teacher'),
                'password' => ('Alblasserdam43'),
                'user_roles' => [1],
                'send_welcome_email' => 1,
                'school_location_id' => $this->schoolLocation->getKey(),
                'demo' => true,
            ]);
        }
        return $user;
    }

    /** student data */
    protected function createDemoStudentsIfNeeded()
    {
        $users = collect([]);
        $userDetails = collect([
            ['nr' => '01', 'dyslexie' => true],
            ['nr' => '02', 'dyslexie' => false],
            ['nr' => '03', 'dyslexie' => false],
            ['nr' => '04', 'dyslexie' => false],
            ['nr' => '05', 'dyslexie' => false],
        ]);
        $userDetails->each(function ($u) use ($users) {
            $u = (object)$u;
            $user = User::where('school_location_id', $this->schoolLocation->getKey())
                ->where('username', $this->getUsername('student', $u->nr))->first();
            if (!$user) {
                $userFactory = new Factory(new User());
                $user = $userFactory->generate([
                    'name_first' => $u->nr,
                    'name' => $u->nr,
                    'external_id' => sprintf('D%s', $u->nr),
                    'username' => $this->getUsername('student', $u->nr),
                    'password' => $u->nr,
                    'text2speech' => (int)$u->dyslexie,
                    'time_dispensation' => (int)$u->dyslexie,
                    'user_roles' => [3],
                    'school_location_id' => $this->schoolLocation->getKey(),
                    'send_welcome_email' => 1,

                    'demo' => true
                ]);

            }

            if ($u->nr === '01') {
                $r = Text2Speech::withTrashed()->firstOrCreate(
                    ['user_id' => $user->getKey()],
                    ['price' => 0.0, 'active' => 1, 'acceptedby' => 0]
                );
                if($r->trashed()){
                    $r->restore();
                }
                $r = Text2SpeechLog::firstOrCreate(
                    ['user_id' => $user->getKey()],
                    ['who' => 0, 'action' => 'ACCEPTED']
                );
            }
            $users->add($user);
        });
        return $users;

    }

    protected function getUsername($type, $nr = null, $customer_code = null)
    {
        $typeName = 'student';
        if ($type == 'teacher') {
            $nr = '';
            $typeName = 'docent';
        }
        if ($customer_code === null) $customer_code = $this->schoolLocation->customer_code;
        return str_replace(' ', '-', sprintf('info+%s%s%s@test-correct.nl', $customer_code, $typeName, $nr));
    }

    /** section data */
    protected function getDemoSection()
    {
        $this->schoolLocation->refresh();
        $sls = $this->schoolLocation->schoolLocationSections->first(function ($s) {
            return $s->section !== null && $s->section->name === self::SECTIONNAME;
        });
        if ($sls) {
            return $sls->section;
        }
        return null;
    }

    protected function createDemoSectionIfNeeded()
    {
        $section = $this->getDemoSection();
        if ($section == null) {
            $section = Section::create(['name' => self::SECTIONNAME, 'demo' => true]);
        }

        $s = SchoolLocationSection::withTrashed()->firstOrCreate(
            [
                'school_location_id' => $this->schoolLocation->getKey(),
                'section_id' => $section->getKey()
            ],
            ['demo' => true,]
        );
        if($s->trashed()){
            $s->restore();
        }
        $this->setSection($section);
        return $section;
    }

    protected function getDemoEducationLevel()
    {
        $attr = [
            'name' => self::EDUCATIONLEVELNAME,
            'max_years' => 1
        ];

        $return = EducationLevel::withTrashed()->firstOrCreate($attr);
        if($return->trashed()){
            $return->restore();
        }
        return $return;
    }

    /** subject data */
    protected function getDemoSubjectIfExists(Section $section)
    {
        return Subject::where('section_id', $section->getKey())
            ->where('name', self::SUBJECTNAME)
            ->first();
    }

    protected function createDemoSubjectIfNeeded()
    {
        $baseSubject = BaseSubject::withTrashed()->firstOrCreate([
            'name' => self::SUBJECTNAME
        ]);
        if($baseSubject->trashed()){
            $baseSubject->restore();
        }
        if (null === $this->section) {
            $this->createDemoSectionIfNeeded();
        }
        $subject = $this->getDemoSubjectIfExists($this->section);

        if ($subject == null) {
            $subject = Subject::create([
                'section_id' => $this->section->getKey(),
                'base_subject_id' => $baseSubject->getKey(),
                'name' => self::SUBJECTNAME,
                'abbreviation' => 'DV',
                'demo' => true,
            ]);
        }

        return $subject;

    }

}
