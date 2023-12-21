<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;

class FactoryScenarioSchoolSimple extends FactoryScenarioSchool
{
    protected $schoolName;

    public $data;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;
    protected $subjectName;
    public $baseSubjectId;

    protected $schoolClassName;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'SimpleSchoolGroup';
        $this->schoolLocationName = 'SimpleSchool';

        $this->baseSubjectId = 1;
        $this->subjectName = 'Nederlandse gramatica';
        $this->sectionName = 'Nederlands';

        $this->schoolClassName = 'SchoolClass1';
    }

    /**
     * Create a complete school scenario with the bare necessities
     * 1 school, 1 school location
     * - without shared sections, one section only
     * - one school year, one period
     * - 1 teacher, 3 students
     *
     * Subject: name 'Nederlandse Gramatica', baseSubjectId '1', section 'Nederlands',
     * One school year, with period from '1 jan / 31 dec'
     */
    public static function create()
    {
        $factory = new static;
        //every subsequent scenario get a new name SimpleSchoolGroup001, SimpleSchoolGroup002, etc.
        $factory->generateUniqueSchoolName();

        //create school
        $school = FactorySchool::create($factory->schoolName)->school;
        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName)->addEducationlevels([1, 2, 3])->schoolLocation;
        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;
        //create section and subject
        $section = static::getSectionWithSubjects($schoolLocation, $factory);

        //create teacher user

        static::addTeachersAndStudents($schoolLocation, $schoolYearLocation, $factory, $section);

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        return $factory;
    }

    protected function generateUniqueSchoolName()
    {
        for ($i = 1; $i < 20; $i++) {
            $uniqueSchoolName = $this->schoolName . sprintf("%03d", $i);
            $uniqueSchoolLocationName = $this->schoolLocationName . sprintf("%03d", $i);
            if (!School::where('name', $uniqueSchoolName)->count() && !SchoolLocation::where('name', $uniqueSchoolLocationName)) {
                $this->schoolName = $uniqueSchoolName;
                $this->schoolLocationName = $uniqueSchoolLocationName;
                break;
            }
        }

    }

    /**
     * @param SchoolLocation $schoolLocation
     * @param FactoryScenarioSchoolSimple $factory
     * @return mixed
     */
    protected static function getSectionWithSubjects(SchoolLocation $schoolLocation, FactoryScenarioSchoolSimple $factory)
    {
        $section = FactorySection::create($schoolLocation, $factory->sectionName)
            ->addSubject(FactoryBaseSubject::find($factory->baseSubjectId), $factory->subjectName)->section;
        return $section;
    }

    /**
     * @param SchoolLocation $schoolLocation
     * @param SchoolYear $schoolYearLocation
     * @param FactoryScenarioSchoolSimple $factory
     * @param mixed $section
     * @return void
     * @throws \Exception
     */
    protected static function addTeachersAndStudents(
        SchoolLocation              $schoolLocation,
        SchoolYear          $schoolYearLocation,
        FactoryScenarioSchoolSimple $factory,
        mixed                       $section
    ): void {
        $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false)->user;
        //create school class with teacher and students records, add the teacher-user, create student-users
        $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
            ->addTeacher($teacherSchoolLocation, $section->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user);
    }
}