<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\BaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryUser;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\SchoolLocation;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\User;

class FactoryScenarioSchoolPersonal extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;

    protected $schoolClassName;

    protected $customer_code;

    protected $dutchTeacher;

    protected $frenchTeacher;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Personal test School';

        $this->schoolLocationName = 'Personal test School location';

        $this->sectionName = 'Personal test section';

        $this->schoolClassName = 'School class Personal test';

        $this->customer_code = 'PERSONAL';
    }

    public static function create()
    {
        $factory = new static;

        $factory->createSimpleSchoolWithOneTeacher();


        return $factory;
    }

    public function getData()
    {
        return parent::getData() + [
                'dutchTeacher'  => $this->dutchTeacher,
                'frenchTeacher' => $this->frenchTeacher,
            ];
    }

    private function createSimpleSchoolWithOneTeacher()
    {
        $this->school = FactorySchool::create($this->schoolName, User::find(520), ['customer_code' => $this->customer_code])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create(
            $this->school,
            'Client School Location',
            [
                'customer_code' => $this->customer_code,
                'user_id'       => '520'
            ]
        )
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $frenchSectionFactory = FactorySection::create($schoolLocation, $this->sectionName);
        $frenchSectionFactory->addSubject(
            BaseSubject::find(BaseSubject::FRENCH),
            'Français'
        );
        $frenchSubject = $frenchSectionFactory->section->subjects()->where('base_subject_id', BaseSubject::FRENCH)->first();

        $this->frenchTeacher = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'teacherFrench@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'French',
            'abbreviation' => 'One',
        ])->user;

        $dutchSectionFactory = FactorySection::create($schoolLocation, $this->sectionName);
        $dutchSectionFactory->addSubject(
            BaseSubject::find(BaseSubject::DUTCH),
            'Nederlands'
        );
        $dutchSubject = $dutchSectionFactory->section->subjects()->where('base_subject_id', BaseSubject::DUTCH)->first();

        $this->dutchTeacher = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'teacherDutch@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Dutch',
            'abbreviation' => 'One',
        ])->user;

        FactorySchoolClass::create($schoolYearLocation, 1, $this->schoolClassName)
            ->addTeacher($this->dutchTeacher, $dutchSubject)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user);


        $this->teachers->add($this->dutchTeacher);
        $this->teachers->add($this->frenchTeacher);

        FactoryTest::create($this->dutchTeacher)
            ->setProperties([
                'name'               => 'test-' . $dutchSubject->name,
                'subject_id'         => $dutchSubject->id,
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag Nederlands gepubliceerd:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);

    }
}
