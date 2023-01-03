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
use tcCore\Rules\EmailDns;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class FactoryScenarioSchoolToetsenbakkerij extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;

    protected $schoolClassName;

    protected $customer_code;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Toetsenbakkerij School';

        $this->schoolLocationName = 'Toetsenbakkerij';

        $this->sectionName = 'Toetsenbakkerij section';

        $this->customer_code = config('custom.TB_customer_code');
    }

    public static function create()
    {
        $factory = new static;
        if (SchoolLocation::where('name', $factory->schoolLocationName)->exists()) {
            throw new \Exception('Toetsenbakkerij school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => $factory->customer_code])
            ->school;

        //create school location, add educationLevels
        $educationLevels = \tcCore\EducationLevel::whereNot('name', 'Demo')->pluck('id')->unique()->toArray();

        $schoolLocation = FactorySchoolLocation::create(
            $school,
            $factory->schoolLocationName,
            [
                'customer_code'                      => $factory->customer_code,
                'user_id'                            => '520',
                'main_address'                       => 'Dotterbloemstraat 25',
                'main_postal'                        => '3053 JV',
                'main_city'                          => 'Rotterdam',
                'main_countery'                      => 'Netherlands',
                'invoice_address'                    => 'Dotterbloemstraat 25',
                'invoice_postal'                     => '3053 JV',
                'invoice_city'                       => 'Rotterdam',
                'invoice_countery'                   => 'Netherlands',
                'visit_address'                      => 'Dotterbloemstraat 25',
                'visit_postal'                       => '3053 JV',
                'visit_city'                         => 'Rotterdam',
                'visit_countery'                     => 'Netherlands',
                'keep_out_of_school_location_report' => true,
                'allow_writing_assignment'           => true,
                'show_national_item_bank'            => true,
                'license_type'                       => SchoolLocation::LICENSE_TYPE_CLIENT
            ]
        )
            ->addEducationlevels($educationLevels)
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('name', 'NOT LIKE', '%CITO%')->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, $baseSubject->name);
        });

        $section = $sectionFactory->section;

        //create bakkerij official author in the correct school
        $bakker = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 't.bakker@test-correct.nl',
            'name_first'   => 'Toetsen',
            'name_suffix'  => '',
            'name'         => 'Bakker',
            'abbreviation' => 'TC',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users

        collect([$bakker])->each(function ($author) use ($section, $schoolLocation, $factory, $schoolYearLocation) {
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