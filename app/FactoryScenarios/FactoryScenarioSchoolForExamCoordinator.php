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

class FactoryScenarioSchoolForExamCoordinator extends FactoryScenarioSchool
{
    protected $schoolName;

    public $data;
    protected $schoolLocationName;
    protected $sectionName;
    protected $subjectName;
    public $baseSubjectId;
    protected $schoolClassName;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Coordinator gang';
        $this->schoolLocationName = 'CoordinatorSchool';

        $this->baseSubjectId = 1;
        $this->subjectName = 'Nederlandse';
        $this->sectionName = 'Nederlands';
        $this->schoolClassName = 'SchoolClass1';
    }

    /**
     * Create a complete school scenario with 2 school locations
     * where school managers can appoint coordinators and switch between them
     */
    public static function create()
    {
        $factory = new static;
        $factory->generateUniqueSchoolName();

        $school = FactorySchool::create($factory->schoolName)->school;

        collect(range(1,2))->each(function ($key) use ($factory, $school) {
            $schoolLocation = FactorySchoolLocation::create(
                $school,
                $factory->schoolLocationName . $key
            )
                ->addEducationlevels([1, 2, 3])
                ->schoolLocation;

            $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'), true)
                ->addPeriodFullYear()
                ->schoolYear;

            $section = FactorySection::create($schoolLocation, $factory->sectionName)
                ->addSubject(FactoryBaseSubject::find($factory->baseSubjectId), $factory->subjectName)->section;

            collect(range(1,2))->each(function ($key) use ($section, $factory, $schoolYearLocation, $schoolLocation) {

                $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false)->user;

                $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
                    ->addTeacher($teacherSchoolLocation, $section->subjects()->first())
                    ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                    ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                    ->addStudent(FactoryUser::createStudent($schoolLocation)->user);
            });
        });


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
}