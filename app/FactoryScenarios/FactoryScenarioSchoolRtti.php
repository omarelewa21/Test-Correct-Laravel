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
use tcCore\Factories\FactoryUmbrellaOrganization;
use tcCore\Factories\FactoryUser;
use tcCore\School;
use tcCore\SchoolLocation;

class FactoryScenarioSchoolRtti extends FactoryScenarioSchool
{

    public $data;
    protected $baseName = 'Rtti';
    protected $schoolName = 'RttiSchool';
    protected $schoolLocationName = 'RttiSchoolLocation';
    protected $sectionName = 'Nederlands';
    protected $subjectName = 'Nederlandse gramatica';
    public $baseSubjectId = 1;
    protected $schoolClassName = 'Rtti class 1';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create the RTTI school location
     */
    public static function create()
    {
        $factory = new static;

        $umbrellaOrganization = FactoryUmbrellaOrganization::create("{$factory->baseName} Organization")->umbrellaOrganization;

        $school = FactorySchool::create(
            schoolName: $factory->schoolName,
            umbrellaOrganization: $umbrellaOrganization
        )->school;

        $accountManager = FactoryUser::createAccountManager()->user;

        $schoolLocation = FactorySchoolLocation::create(
            $school,
            $factory->schoolLocationName,
            [
                'id'      => 2,
                'user_id' => $accountManager->getKey()
            ]
        )
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        $schoolYearForLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()
            ->schoolYear;

        $section = FactorySection::create($schoolLocation, $factory->sectionName)
            ->addSubject(FactoryBaseSubject::find($factory->baseSubjectId), $factory->subjectName)
            ->section;

        $schoolManager = FactoryUser::createSchoolManager(
            $schoolLocation,
            [
                'username'     => 'rtti-schoolbeheerder@test-correct.nl',
                'session_hash' => sprintf('%s%d', Str::random(85), 123)
            ]
        );

        $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false)
            ->user;

        $schoolClassLocation = FactorySchoolClass::create($schoolYearForLocation, 1, $factory->schoolClassName)
            ->addTeacher($teacherSchoolLocation, $section->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user);

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        return $factory;
    }

}