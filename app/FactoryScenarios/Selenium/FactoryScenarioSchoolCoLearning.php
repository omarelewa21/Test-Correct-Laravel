<?php

namespace tcCore\FactoryScenarios\Selenium;

use Carbon\Carbon;
use Illuminate\Support\Str;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioSchool;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithOpenQuestions;
use tcCore\School;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;

class FactoryScenarioSchoolCoLearning extends FactoryScenarioSchool
{
    protected $schoolName;

    public $data;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;
    protected $subjectName;
    public $baseSubjectId;

    protected $schoolClassName;
    protected Test $test;

    protected TestTake $testTake;
    protected static string $prefix = 'CoLearning';

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = self::$prefix.'School';
        $this->schoolLocationName = self::$prefix.'SchoolLocation';

        $this->baseSubjectId = 1;
        $this->subjectName = 'Nederlandse gramatica';
        $this->sectionName = 'Nederlands';

        $this->schoolClassName = self::$prefix.'Klas';
    }

    /**
     * Create a complete school scenario with the bare necessities for CO-Learning
     * 1 school, 1 school location
     * - without shared sections, one section only
     * - one school year, one period
     * - 1 teacher, 5 students
     *
     * Subject: name 'Nederlandse Gramatica', baseSubjectId '1', section 'Nederlands',
     * One school year, with period from '1 jan / 31 dec'
     */
    public static function create()
    {
        $factory = new static;
//        $factory->generateUniqueSchoolName();

        $schoolIteratorNumber = $factory->getSchoolIteratorNumber() + 1;

        //create school
        $school = FactorySchool::create($factory->schoolName . $schoolIteratorNumber)->school;
        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName . $schoolIteratorNumber)->addEducationlevels([1, 2, 3])->schoolLocation;
        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;
        //create section and subject
        $section = FactorySection::create($schoolLocation, $factory->sectionName)
            ->addSubject(FactoryBaseSubject::find($factory->baseSubjectId), $factory->subjectName)->section;


        //create teacher user
        $docentIterator = $factory->getDocentIteratorNumber(); //get highest previously generated user 'number'
        $studentIterator = $factory->getStudentIteratorNumber();

        $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false, [
            'username' => sprintf('%s_Docent%d@test-correct.test', self::$prefix, ++$docentIterator),
            'name'     => sprintf('%s Docent %d', self::$prefix, $docentIterator),
        ])->user;
        //create school class with teacher and students records, add the teacher-user, create student-users
        $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
            ->addTeacher($teacherSchoolLocation, $section->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('%s_Student%d@test-correct.test',self::$prefix, ++$studentIterator),
                'name'     => sprintf('%s Student %d',self::$prefix, $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('%s_Student%d@test-correct.test',self::$prefix, ++$studentIterator),
                'name'     => sprintf('%s Student %d',self::$prefix, $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('%s_Student%d@test-correct.test',self::$prefix, ++$studentIterator),
                'name'     => sprintf('%s Student %d',self::$prefix, $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('%s_Student%d@test-correct.test',self::$prefix, ++$studentIterator),
                'name'     => sprintf('%s Student %d',self::$prefix, $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('%s_Student%d@test-correct.test',self::$prefix, ++$studentIterator),
                'name'     => sprintf('%s Student %d',self::$prefix, $studentIterator),
            ])->user);

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        $factory->createTestTake($teacherSchoolLocation);

        $schoolLocation->allow_new_co_learning = true;
        $schoolLocation->allow_new_co_learning_teacher = true;

        return $factory;
    }


    protected function getStudentIteratorNumber()
    {
        return $this->getUserIteratorNumber('Student');
    }

    protected function getDocentIteratorNumber()
    {
        return $this->getUserIteratorNumber('Docent');
    }

    protected function getUserIteratorNumber($needle)
    {
        $users = User::where('username', 'LIKE', self::$prefix.'%')->get()->map->username;

        return $users->filter(fn($username) => Str::contains($username, $needle))
            ->reduce(function ($carry, $username) use ($needle) {
                $iterator = Str::between($username, $needle, '@');
                if ($carry < $iterator) {
                    $carry = $iterator;
                };
                return $carry;
            }, 0);
    }

    protected function getSchoolIteratorNumber()
    {
        $schools = School::where('name', 'LIKE', self::$prefix.'%')->get()->map->name;

        return $schools->reduce(function ($carry, $name) {
            $iterator = Str::afterLast($name, 'School');
            if ($carry < $iterator) {
                $carry = $iterator;
            };
            return $carry;
        }, 0);
    }

    protected function createTestTake(User $teacherUser): void
    {
        //create test
        $this->test = $test = FactoryScenarioTestTestWithOpenQuestions::createTest(
            testName: self::$prefix.' Test open questions . ' . Carbon::now()->format('ymd-Hi'),
            user    : $teacherUser,
        );

        //create taken testtake
        $this->testTake = FactoryScenarioTestTakeTaken::createTestTake(
            user    : $teacherUser,
            testName: $test->name,
            test    : $test,
        );
        $this->testTake->subject_name = $this->test->subject()->value('name');
    }

    public function getData()
    {
        $this->testTake->load(['test', 'test.testQuestions' => function($query) {
            $query->orderBy('order', 'asc');
        }]);
        return [... parent::getData(), 'test_take'=> $this->testTake];
    }

    protected function transformModelToArray($model)
    {
        if ($model instanceof Test) {
            return [
                'id'    => $model->getKey(),
                'uuid'  => $model->uuid,
                'title' => $model->title,
            ];
        }

        return parent::transformModelToArray($model);

    }
}
