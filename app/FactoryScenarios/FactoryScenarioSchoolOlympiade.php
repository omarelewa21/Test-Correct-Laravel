<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\User;

class FactoryScenarioSchoolOlympiade extends FactoryScenarioSchool
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

        $this->schoolName = 'Olympiade content';

        $this->schoolLocationName = 'Olympiade content';

        $this->sectionName = 'Olympiade section';

        $this->schoolClassName = 'Olympiade school class';

        $this->customer_code = config('custom.olympiade_school_customercode');
    }

    public static function create()
    {
        $factory = self::createOlympiadeSchool();

        self::createSimpleSchoolWithOneTeacher($factory);

        return $factory;
    }

    public function createUsernameForSecondUser($username): string
    {
        return Arr::join([
            Str::before($username, '@'),
            '-B',
            '@',
            Str::after($username, '@'),
        ], '');
    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
            ];
    }
    private static function createSimpleSchoolWithOneTeacher(FactoryScenarioSchoolOlympiade $factory)
    {
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'ABC'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create(
            $school,
            'Client School Location Olympiade',
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
            $baseSubject = BaseSubject::find(BaseSubject::DUTCH),
            $schoolLocation->name .'-'.$baseSubject->name
        );


        $section = $sectionFactory->section;

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

    private static function createOlympiadeSchool(): FactoryScenarioSchoolOlympiade
    {
        $factory = new static;
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new \Exception('Olympiade school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520),
            ['customer_code' => $factory->customer_code])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName,
            ['customer_code' => $factory->customer_code, 'user_id' => '520'])
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int) Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('name', 'NOT LIKE', '%CITO%')->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, 'Olympiade-'.$baseSubject->name);
        });

        $section = $sectionFactory->section;

        $subjectDutch = $section->subjects()->where('base_subject_id', BaseSubject::DUTCH)->first();

        //create Olympiade official author user and a secondary teacher in the correct school
        $olympiadeAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => config('custom.olympiade_school_author'),
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Olympiade',
            'abbreviation' => 'TOA',
        ])->user;
        $olympiadeAuthorB = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => $factory->createUsernameForSecondUser(config('custom.olympiade_school_author')),
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher OlympiadeB',
            'abbreviation' => 'TOB',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users

        collect([$olympiadeAuthor, $olympiadeAuthorB])->each(function ($author) use (
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

        FactoryTest::create($olympiadeAuthor)
            ->setProperties([
                'name'               => 'test-'.$subjectDutch->name,
                'subject_id'         => $subjectDutch->id,
                'abbreviation'       => OlympiadeService::getPublishAbbreviation(),
                'scope'              => OlympiadeService::getPublishScope(),
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag Nederlands gepubliceerd Olympiade:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);

        return $factory;
    }
}
