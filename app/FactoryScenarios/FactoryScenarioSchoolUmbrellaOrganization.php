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

class FactoryScenarioSchoolUmbrellaOrganization extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;

    protected $sectionName;

    protected $schoolClassName;

    protected $customer_code;

    protected $teacherOne;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Umbrella test School';

        $this->schoolLocationName = 'Umbrella test School location';

        $this->sectionName = 'Umbrella test section';

        $this->schoolClassName = 'School class Umbrella test';

        $this->customer_code = 'Umbrella';
    }

    public static function create()
    {
        $factory = new static;

        $schoolLocation = $factory->createSimpleSchoolWithOneTeacher();
        $factory->createSchoolInUmbrellaWithSharedSection($schoolLocation);

        return $factory;
    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'  => $this->teacherOne,
                'teacherUmbrella' => $this->teacherUmbrella,
            ];
    }

    private function createSchoolInUmbrellaWithSharedSection($clientSchoolLocation)
    {

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create(
            $this->school,
            'Umbrella School Location',
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
        $sectionFactory = FactorySection::create($schoolLocation, $this->sectionName ."Umbrella");
        $sectionFactory->addSubject(
            BaseSubject::find(BaseSubject::DUTCH),
            'Nederlands'
        );
        $sectionFactory->addSharedSchoolLocation($clientSchoolLocation);


        $subject = $sectionFactory->section->subjects()->where('base_subject_id', BaseSubject::DUTCH)->first();

        $this->teacherUmbrella = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'teacherDutchumbrella@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Dutch Umbrella',
            'abbreviation' => 'One',
        ])->user;

        FactorySchoolClass::create($schoolYearLocation, 1, $this->schoolClassName)
            ->addTeacher($this->teacherUmbrella, $subject)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user);


        $this->teachers->add($this->teacherUmbrella);

        FactoryTest::create($this->teacherUmbrella)
            ->setProperties([
                'name'               => 'test-' . $subject->name,
                'subject_id'         => $subject->id,
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag Nederlands gepubliceerd:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);

    }

    private function createSimpleSchoolWithOneTeacher()
    {
        $this->school = FactorySchool::create('Umbrella school location', User::find(520), ['customer_code' => 'UMBI'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
     $schoolLocation = FactorySchoolLocation::create(
            $this->school,
            'Client Umbrella School Location',
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

        $sectionFactory = FactorySection::create($schoolLocation, $this->sectionName ."Client Umbrella");
        $sectionFactory->addSubject(
            BaseSubject::find(BaseSubject::DUTCH),
            'Nederlands'
        );
        $subject = $sectionFactory->section->subjects()->where('base_subject_id', BaseSubject::DUTCH)->first();

        $this->teacherOne = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'teacher@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Dutch Umbrella Client',
            'abbreviation' => 'One',
        ])->user;

        FactorySchoolClass::create($schoolYearLocation, 1, $this->schoolClassName. 'aaa')
            ->addTeacher($this->teacherOne, $subject)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user);

        $this->teachers->add($this->teacherOne);

        return $schoolLocation;
    }
}
