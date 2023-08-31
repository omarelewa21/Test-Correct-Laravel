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
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryUser;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\User;

class FactoryScenarioSchoolThiemeMeulenhoff extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;

    protected $schoolClassName;

    protected $customer_code;

    protected $teacher_one;
    protected $school_location_one;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Thieme Meulenhoff vragencontent';

        $this->schoolLocationName = 'Thieme Meulenhoff vragencontent';

        $this->sectionName = 'Thieme Meulenhoff section';

        $this->schoolClassName = 'Thieme Meulenhoff school class';

        $this->customer_code = 'THIEMEMEULENHOFF';
    }

    public static function create()
    {
        $factory = new static;

        self::createThiemeMeulenHoffSchool($factory);
        self::createSimpleSchoolWithOneTeacher($factory);


        return $factory;
    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
                'school_location_one' => $this->school_location_one,
            ];
    }

    /**
     * @param  FactoryScenarioSchoolThiemeMeulenhoff  $factory
     * @return void
     * @throws \Exception
     */
    private static function createThiemeMeulenHoffSchool(FactoryScenarioSchoolThiemeMeulenhoff $factory): void
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new \Exception('Thieme Meulenhoff school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'THIEMEMEULENHOFF'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName,
            ['customer_code' => 'THIEMEMEULENHOFF', 'user_id' => '520'])
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int) Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('name', 'NOT LIKE', '%CITO%')->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, 'ThiemeMeulenhoff-'.$baseSubject->name);
        });

        $section = $sectionFactory->section;
        $subjectDutch = $section->subjects()->where('base_subject_id', BaseSubject::DUTCH)->first();

        //create Thieme Meulenhoff official author user and a secondary teacher in the correct school
        $thiemeMeulenhoff = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'info+tmontwikkelaar@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Thieme Meulenhoff',
            'abbreviation' => 'TC',
        ])->user;
        $thiemeMeulenhoffB = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'info+bak-TM@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Thieme Meulenhoff B',
            'abbreviation' => 'TC',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users

        collect([$thiemeMeulenhoff, $thiemeMeulenhoffB])->each(function ($author) use (
            $section,
            $schoolLocation,
            $factory,
            $schoolYearLocation
        ) {
            $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
                ->addTeacher($author, $section->subjects()->first())
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user);
        });

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        FactoryTest::create($thiemeMeulenhoff)
            ->setProperties([
                'name'               => 'test-'.$subjectDutch->name,
                'subject_id'         => $subjectDutch->id,
                'abbreviation'       => ThiemeMeulenhoffService::getPublishAbbreviation(),
                'scope'              => ThiemeMeulenhoffService::getPublishScope(),
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag Nederlands gepubliceerd:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);

    }

    private static function createSimpleSchoolWithOneTeacher(FactoryScenarioSchoolThiemeMeulenhoff $factory)
    {
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'THIEMEMEULENHOFF'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create(
            $school,
            'Client School Location',
            ['customer_code' => 'THIEMEMEULENHOFF', 'user_id' => '520']
        )
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int) Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);


        $sectionFactory->addSubject(
            BaseSubject::find(BaseSubject::DUTCH),
            'Nederlands'
        );


        $section = $sectionFactory->section;

        //create Thieme Meulenhoff official author user and a secondary teacher in the correct school
        $factory->teacher_one = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'teacherOne@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher One',
            'abbreviation' => 'One',
        ])->user;

        FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
            ->addTeacher($factory->teacher_one, $section->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user);


        $factory->teachers->add($factory->teacher_one);
    }
}
