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
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\User;

class FactoryScenarioSchoolCreathlon extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;

    protected $schoolClassName;

    protected $customer_code;

    protected $teacher_one;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Creahtlon vragencontent';

        $this->schoolLocationName = 'Creahtlon vragencontent';

        $this->sectionName = 'Creathlon section';

        $this->schoolClassName = 'Creathlon school class';

        $this->customer_code = 'CREATHLON';
    }

    public static function create()
    {
        $factory = new static;

        self::createCreathlonSchool($factory);
        self::createSimpleSchoolWithOneTeacher($factory);

        return $factory;
    }

    private static function createCreathlonSchool(FactoryScenarioSchoolCreathlon $factory)
    {
        if(SchoolLocation::where('name', $factory->schoolName)->exists()){
            throw new \Exception('Creathlon school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'CREATHLON'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName, ['customer_code' => 'CREATHLON', 'user_id' => '520'])
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('id', 23)->get()->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, 'Creathlon-'.$baseSubject->name);
        });

        $section = $sectionFactory->section;
        $subjectFrench = $section->subjects()->where('base_subject_id', BaseSubject::FRENCH)->first();


        //create creathlon official author user and a secondary teacher in the correct school
        $creathlonAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username' => 'info+creathlonontwikkelaar@test-correct.nl',
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher Creathlon',
            'abbreviation'       => 'TC',
        ])->user;
        $creathlonAuthorB = FactoryUser::createTeacher($schoolLocation, false, [
            'username' => 'info+creathlonontwikkelaarB@test-correct.nl',
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher CreathlonB',
            'abbreviation'       => 'TC',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users

        collect([$creathlonAuthor, $creathlonAuthorB])->each(function($author) use ($section, $schoolLocation, $factory, $schoolYearLocation) {
            $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
                ->addTeacher($author, $section->subjects()->first())
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
                ->addStudent(FactoryUser::createStudent($schoolLocation)->user);
        });

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        FactoryTest::create($creathlonAuthor)
            ->setProperties([
                'name'               => 'test-'.$subjectFrench->name,
                'subject_id'         => $subjectFrench->id,
                'abbreviation'       => CreathlonService::getPublishAbbreviation(),
                'scope'              => CreathlonService::getPublishScope(),
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag CREATHLON gepubliceerd:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);

    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
            ];
    }

    private static function createSimpleSchoolWithOneTeacher(FactoryScenarioSchoolCreathlon $factory)
    {
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'ABC'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create(
            $school,
            'Client School Location Creathlon',
            ['customer_code' => 'ABC', 'user_id' => '520']
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
            $baseSubject = BaseSubject::find(BaseSubject::FRENCH),
            $schoolLocation->name .'-'.$baseSubject->name
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
