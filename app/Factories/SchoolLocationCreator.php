<?php

namespace tcCore\Factories;

use Carbon\Carbon;
use Exception;
use tcCore\BaseSubject;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\FactoryScenarios\FactoryScenarioSchool;
use tcCore\FactoryScenarios\FactoryScenarioSchoolCreathlon;
use tcCore\FactoryScenarios\FactoryScenarioSchoolFormidable;
use tcCore\FactoryScenarios\FactoryScenarioSchoolNationalItemBank;
use tcCore\FactoryScenarios\FactoryScenarioSchoolOlympiade;
use tcCore\FactoryScenarios\FactoryScenarioSchoolThiemeMeulenhoff;
use tcCore\SchoolLocation;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\Subject;
use tcCore\User;

class SchoolLocationCreator
{
    public static function createFormidableSchool(FactoryScenarioSchoolFormidable $factory)
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new Exception('Formidable school allready exists');
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
            $factory
        ) {
            self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $author);
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

    public static function createSimpleSchoolWithOneTeacher(FactoryScenarioSchool $factory)
    {
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'ABC'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create(
            $school,
            'Client School Location '.$factory->schoolLocationName,
            ['customer_code' => 'ABC', 'user_id' => '520']
        )
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;


        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);


        $sectionFactory->addSubject(
            $baseSubject = BaseSubject::find($factory->baseSubjectId),
            $schoolLocation->name.'-'.$baseSubject->name
        );


        $subject = $sectionFactory->section->subjects()->where('base_subject_id', $factory->baseSubjectId)->first();

        $factory->teacher_one = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'teacherOne@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher One',
            'abbreviation' => 'One',
        ])->user;

        self::buildSchoolClass($factory->schoolClassName, $subject, $factory->teacher_one);
    }

    public static function createOlympiadeSchool($factory): FactoryScenarioSchoolOlympiade
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new Exception('Olympiade school allready exists');
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

        $subjectDutch = $section->subjects()->where('base_subject_id', $factory->baseSubjectId)->first();

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
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $olympiadeAuthor);
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $olympiadeAuthorB);



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

    public static function createOlympiadeArchiveSchool($factory): FactoryScenarioSchoolOlympiadeArchive
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new Exception('Olympiade Archive school allready exists');
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
            $sectionFactory->addSubject($baseSubject, 'Olympiade-archive-'.$baseSubject->name);
        });

        $section = $sectionFactory->section;

        $subjectDutch = $section->subjects()->where('base_subject_id', $factory->baseSubjectId)->first();

        //create Olympiade official author user and a secondary teacher in the correct school
        $olympiadeAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => config('custom.olympiade_archive_school_author'),
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Olympiade Archive',
            'abbreviation' => 'TOAA',
        ])->user;
        $olympiadeAuthorB = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => $factory->createUsernameForSecondUser(config('custom.olympiade_archive_school_author')),
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Olympiade ArchiveB',
            'abbreviation' => 'TOAB',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $olympiadeAuthor);
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $olympiadeAuthorB);



        $factory->school = $school->refresh();
        $factory->schools->add($school);

        FactoryTest::create($olympiadeAuthor)
            ->setProperties([
                'name'               => 'test-'.$subjectDutch->name,
                'subject_id'         => $subjectDutch->id,
                'abbreviation'       => OlympiadeArchiveService::getPublishAbbreviation(),
                'scope'              => OlympiadeArchiveService::getPublishScope(),
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag Nederlands gepubliceerd Olympiade Archief:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);

        return $factory;
    }

    public static function createNationalItemBankSchool(FactoryScenarioSchoolNationalItemBank $factory)
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new Exception('Formidable school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'TBNI'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName,
            ['customer_code' => 'TBNI', 'user_id' => '520'])
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int) Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('id', BaseSubject::DUTCH)->get()->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, 'TBNI-'.$baseSubject->name);
        });

        $section = $sectionFactory->section;
        $subjectFrench = $section->subjects()->where('base_subject_id', BaseSubject::DUTCH)->first();

        //create formidable official author user and a secondary teacher in the correct school
        $ontwikkerlaarAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => config('custom.national_item_bank_school_author'),
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Ontwikkelaar Nationaal item bank',
            'abbreviation' => 'TC',
        ])->user;
        $toetsenBakkerAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'info+bak@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher ToetsenBakker Nationaal item bank',
            'abbreviation' => 'TC Bakker',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $ontwikkerlaarAuthor);
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $toetsenBakkerAuthor);


        $factory->school = $school->refresh();
        $factory->schools->add($school);

        FactoryTest::create($ontwikkerlaarAuthor)
            ->setProperties([
                'name'               => 'test-'.$subjectFrench->name,
                'subject_id'         => $subjectFrench->id,
                'abbreviation'       => 'LDT',
                'scope'              => 'ldt',
                'education_level_id' => '1',
                'draft'              => false,
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create()->setProperties([
                    "question" => '<p>voorbeeld vraag Nationale itembank gepubliceerd:</p> <p>wat is de waarde van pi</p> ',
                ]),
            ]);


    }

    /**
     * @param  FactoryScenarioSchoolThiemeMeulenhoff  $factory
     * @return void
     * @throws Exception
     */
    public static function createThiemeMeulenHoffSchool(FactoryScenarioSchoolThiemeMeulenhoff $factory): void
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new Exception('Thieme Meulenhoff school allready exists');
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
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $thiemeMeulenhoff);
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $thiemeMeulenhoffB);

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

    public static function createCreathlonSchool(FactoryScenarioSchoolCreathlon $factory)
    {
        if (SchoolLocation::where('name', $factory->schoolName)->exists()) {
            throw new Exception('Creathlon school allready exists');
        }

        //create school
        $school = FactorySchool::create($factory->schoolName, User::find(520), ['customer_code' => 'CREATHLON'])
            ->school;

        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, $factory->schoolLocationName,
            ['customer_code' => 'CREATHLON', 'user_id' => '520'])
            ->addEducationlevels([1, 2, 3])
            ->schoolLocation;

        //create school year and full year period for the current year
        $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int) Carbon::today()->format('Y'), true)
            ->addPeriodFullYear()
            ->schoolYear;

        //create section and subject
        $sectionFactory = FactorySection::create($schoolLocation, $factory->sectionName);

        BaseSubject::where('id', $factory->baseSubjectId)->get()->each(function ($baseSubject) use ($sectionFactory) {
            $sectionFactory->addSubject($baseSubject, 'Creathlon-'.$baseSubject->name);
        });

        $section = $sectionFactory->section;
        $subjectFrench = $section->subjects()->where('base_subject_id', $factory->baseSubjectId)->first();


        //create creathlon official author user and a secondary teacher in the correct school
        $creathlonAuthor = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'info+creathlonontwikkelaar@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher Creathlon',
            'abbreviation' => 'TC',
        ])->user;
        $creathlonAuthorB = FactoryUser::createTeacher($schoolLocation, false, [
            'username'     => 'info+creathlonontwikkelaarB@test-correct.nl',
            'name_first'   => 'Teacher',
            'name_suffix'  => '',
            'name'         => 'Teacher CreathlonB',
            'abbreviation' => 'TC',
        ])->user;

        //create school class with teacher and students records, add the teacher-user, create student-users
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $creathlonAuthor);
        self::buildSchoolClass($factory->schoolClassName, $section->subjects()->first(), $creathlonAuthorB);

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

    private static function buildSchoolClass(
        $schoolClassName,
        Subject $subject,
        User $teacher,
        \tcCore\SchoolYear $schoolYearLocation = null
    ): void {
        if (is_null($schoolYearLocation)) {
            //create school year and full year period for the current year
            $schoolYearLocation = FactorySchoolYear::create($teacher->schoolLocation,
                (int) Carbon::today()->format('Y'), true)
                ->addPeriodFullYear()
                ->schoolYear;
        }

        FactorySchoolClass::create($schoolYearLocation, 1, $schoolClassName)
            ->addTeacher($teacher, $subject)
            ->addStudent(FactoryUser::createStudent($teacher->schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($teacher->schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($teacher->schoolLocation)->user);
    }
}
