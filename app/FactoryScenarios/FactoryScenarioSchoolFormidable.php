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
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\User;

class FactoryScenarioSchoolFormidable extends FactoryScenarioSchool
{
    protected $schoolName;

    protected $schoolLocationName;

    protected $sectionName;

    protected $schoolClassName;

    protected $customer_code;

    protected $teacher_one;
    protected $school_location_one;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Formidable vragencontent';

        $this->schoolLocationName = 'Formidable vragencontent';

        $this->sectionName = 'Formidable section';

        $this->schoolClassName = 'Formidable school class';

        $this->customer_code = 'FORMIDABLE';
    }

    public static function create()
    {
        $factory = new static;

        self::createFormidableSchool($factory);
        self::createSimpleSchoolWithOneTeacher($factory);

        return $factory;
    }

    private static function createFormidableSchool(FactoryScenarioSchoolFormidable $factory)
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new \Exception('Formidable school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'FORMIDABLE'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName,
            ['customer_code' => 'FORMIDABLE', 'user_id' => '520'])
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int) Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('id', 23)->get()->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, 'Formidable-'.$baseSubject->name);
        });

        $section = $sectionFactory->section;
        $subjectFrench = $section->subjects()->where('base_subject_id', BaseSubject::FRENCH)->first();

        //create formidable official author user and a secondary teacher in the correct school
        $formidableAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'info+fdontwikkelaar@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Formidable',
            'abbreviation' => 'TC',
        ])->user;
        $formidableAuthorB = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'info+bak-FD@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher FormidableB',
            'abbreviation' => 'TC',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users

        collect([$formidableAuthor, $formidableAuthorB])->each(function ($author) use (
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

        FactoryTest::create($formidableAuthor)
            ->setProperties([
                'name'               => 'test-'.$subjectFrench->name,
                'subject_id'         => $subjectFrench->id,
                'abbreviation'       => FormidableService::getPublishAbbreviation(),
                'scope'              => FormidableService::getPublishScope(),
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag FORMIDABLE! gepubliceerd:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);


    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
                'school_location_one' => $this->school_location_one,
            ];
    }

    private static function createSimpleSchoolWithOneTeacher(FactoryScenarioSchoolFormidable $factory)
    {
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'ABC'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create(
            $school,
            'Client School Location Formidable',
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
