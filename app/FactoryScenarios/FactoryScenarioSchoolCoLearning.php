<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use Illuminate\Support\Str;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\School;
use tcCore\SchoolLocation;
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
    protected $baseSubjectId;

    protected $schoolClassName;
    private Test $test;

    private TestTake $testTake;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'CoLearningSchool';
        $this->schoolLocationName = 'CoLearningSchoolLocation';

        $this->baseSubjectId = 1;
        $this->subjectName = 'Nederlandse gramatica';
        $this->sectionName = 'Nederlands';

        $this->schoolClassName = 'CoLearningKlas';
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
            'username' => sprintf('CoLearning_Docent%d@test-correct.test', ++$docentIterator),
            'name'     => sprintf('CoLearning Docent %d', $docentIterator),
        ])->user;
        //create school class with teacher and students records, add the teacher-user, create student-users
        $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
            ->addTeacher($teacherSchoolLocation, $section->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('CoLearning_Student%d@test-correct.test', ++$studentIterator),
                'name'     => sprintf('CoLearning Student %d', $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('CoLearning_Student%d@test-correct.test', ++$studentIterator),
                'name'     => sprintf('CoLearning Student %d', $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('CoLearning_Student%d@test-correct.test', ++$studentIterator),
                'name'     => sprintf('CoLearning Student %d', $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('CoLearning_Student%d@test-correct.test', ++$studentIterator),
                'name'     => sprintf('CoLearning Student %d', $studentIterator),
            ])->user)->addStudent(FactoryUser::createStudent($schoolLocation, [
                'username' => sprintf('CoLearning_Student%d@test-correct.test', ++$studentIterator),
                'name'     => sprintf('CoLearning Student %d', $studentIterator),
            ])->user);

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        $factory->createCoLearningTestTake($teacherSchoolLocation);

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
        $users = User::where('username', 'LIKE', 'CoLearning%')->get()->map->username;

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
        $schools = School::where('name', 'LIKE', 'CoLearning%')->get()->map->name;

        return $schools->reduce(function ($carry, $name) {
            $iterator = Str::afterLast($name, 'School');
            if ($carry < $iterator) {
                $carry = $iterator;
            };
            return $carry;
        }, 0);
    }

    protected function createCoLearningTestTake(User $teacherUser)
    {
        //create test
        $this->test = $test = FactoryScenarioTestTestWithOpenQuestions::createTest(
            user: $teacherUser,
            testName: 'CO-Learning Test open questions . ' . Carbon::now()->format('ymd-Hi'),
        );

        //create taken testtake
        $this->testTake = FactoryScenarioTestTakeTaken::createTestTake(
            user: $teacherUser,
            test: $test,
            testName: $test->name,
        );
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
