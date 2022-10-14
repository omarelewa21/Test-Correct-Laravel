<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\BaseSubject;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class FactoryScenarioSchoolCito extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;

    protected $schoolClassName;

    protected $customer_code;

    protected $teacherOneUsername = 'teacher-cito@test-correct.nl';
    protected $teacherTwoUsername = 'teacher-cito-b@test-correct.nl';

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Cito Scholengemeenschap';

        $this->schoolLocationName = "Cito schoollocatie";

        $this->sectionName = 'Cito sectie';

        $this->schoolClassName = 'Cito schoolclass';

        $this->customer_code = 'CITO-TOETSENOPMAAT';
    }

    public static function create()
    {
        $factory = new static;
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new \Exception('Cito school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => $factory->customer_code])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName, ['customer_code' => $factory->customer_code, 'user_id' => '520'])
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('name', 'NOT LIKE', '%CITO%')->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, 'cito-' . $baseSubject->name);
        });

        $section = $sectionFactory->section;

        //create cito official author user and a secondary teacher in the correct school
        $citoAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => $factory->teacherOneUsername,
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Cito',
            'abbreviation' => 'TCA',
        ])->user;
        $citoAuthorB = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => $factory->teacherTwoUsername,
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Cito B',
            'abbreviation' => 'TCB',
        ])->user;

        //TODO: Refactor this into a reusable portion of code --RR
        //create school class with teacher and students records, add the teacher-user, create student-users
        collect([$citoAuthor, $citoAuthorB])->each(function ($author) use ($section, $schoolLocation, $factory, $schoolYearLocation) {
            $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
                ->addTeacher($author, $section->subjects()->first())
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user);
        });
        $factory->school = $school->refresh();
        $factory->schools->add($school);

        return $factory;
    }
}