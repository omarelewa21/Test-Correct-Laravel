<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithAllQuestionTypes;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\Role;
use tcCore\SchoolYear;
use tcCore\User;
use tcCore\BaseSubject;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\SchoolLocation;
use tcCore\Test;


class SqLiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addBaseSubjects();
        $this->addUserRoles();
        $this->addEducationLevels();
        $this->addTestTakeStatuses();
        $this->addTestTakeEventTypes();
//        $this->addNationalItemBank();
    }

    private function addBaseSubjects()
    {
        $data = [
            ['id' => 1, 'name' => 'Nederlands',],
            ['id' => 2, 'name' => 'Fries',],
            ['id' => 3, 'name' => 'Grieks',],
            ['id' => 4, 'name' => 'Latijn',],
            ['id' => 5, 'name' => 'WiskundeA',],
            ['id' => 6, 'name' => 'WiskundeB',],
            ['id' => 7, 'name' => 'WiskundeC',],
            ['id' => 8, 'name' => 'WiskundeD',],
            ['id' => 9, 'name' => 'Natuurkunde',],
            ['id' => 10, 'name' => 'Scheikunde',],
            ['id' => 11, 'name' => 'Biologie',],
            ['id' => 12, 'name' => 'ANW',],
            ['id' => 13, 'name' => 'NLT',],
            ['id' => 14, 'name' => 'Informatica',],
            ['id' => 15, 'name' => 'Geschiedenis',],
            ['id' => 16, 'name' => 'Aardrijkskunde',],
            ['id' => 17, 'name' => 'Economie',],
            ['id' => 18, 'name' => 'Maatschappijwetenschappen',],
            ['id' => 19, 'name' => 'Maatschappijleer',],
            ['id' => 20, 'name' => 'Managementenorganisatie',],
            ['id' => 21, 'name' => 'Filosofie',],
            ['id' => 22, 'name' => 'Engels',],
            ['id' => 23, 'name' => 'Frans',],
            ['id' => 24, 'name' => 'Duits',],
            ['id' => 25, 'name' => 'Spaans',],
            ['id' => 26, 'name' => 'Wiskunde',],
            ['id' => 27, 'name' => 'NASK1',],
            ['id' => 28, 'name' => 'NASK2',],
            ['id' => 29, 'name' => 'Maatschappijleer2',],
            ['id' => 30, 'name' => 'Demovak',],
            ['id' => 33, 'name' => 'CITO-Economie',],
            ['id' => 36, 'name' => 'CITO-Natuurkunde',],
            ['id' => 39, 'name' => 'CITO-WiskundeA',],
            ['id' => 42, 'name' => 'CITO-Nask1',],
            ['id' => 45, 'name' => 'CITO-Nask2',],
            ['id' => 48, 'name' => 'CITO-Aardrijkskunde',],
            ['id' => 51, 'name' => 'CITO-Biologie',],
            ['id' => 54, 'name' => 'CITO-Geschiedenis',],
            ['id' => 57, 'name' => 'CITO-Scheikunde',],
            ['id' => 60, 'name' => 'CITO-Wiskunde',],
            ['id' => 63, 'name' => 'CITO-WiskundeB',],
            ['id' => 66, 'name' => 'NCLOLactatiekunde',],
            ['id' => 67, 'name' => 'Arabisch',],
            ['id' => 68, 'name' => 'Bedrijfseconomie',],
            ['id' => 69, 'name' => 'Beeldendevorming',],
            ['id' => 70, 'name' => 'Bewegen,SportenMaatschappij',],
            ['id' => 71, 'name' => 'Chinees',],
            ['id' => 72, 'name' => 'Cultureleenkunstzinnigevorming',],
            ['id' => 73, 'name' => 'Drama',],
            ['id' => 74, 'name' => 'Dans',],
            ['id' => 75, 'name' => 'Informatietechnologie',],
            ['id' => 76, 'name' => 'Italiaans',],
            ['id' => 77, 'name' => 'Kunst(algemeen)',],
            ['id' => 78, 'name' => 'Kunstvakkenincl.CKV',],
            ['id' => 79, 'name' => 'Lichamelijkeopvoeding',],
            ['id' => 80, 'name' => 'Lichamelijkeopvoeding2',],
            ['id' => 81, 'name' => 'Maatschappijkunde',],
            ['id' => 82, 'name' => 'Muziek',],
            ['id' => 83, 'name' => 'Russisch',],
            ['id' => 84, 'name' => 'Turks',],
            ['id' => 85, 'name' => 'Dienstverleningenproducten',],
            ['id' => 86, 'name' => 'Media,vormgevingenICT',],
            ['id' => 87, 'name' => 'Zorgenwelzijn',],
            ['id' => 88, 'name' => 'Produceren,installerenenenergie',],
            ['id' => 89, 'name' => 'Bouwen,woneneninterieur',],
            ['id' => 90, 'name' => 'Mobiliteitentransport',],
            ['id' => 91, 'name' => 'Horeca,bakkerijenrecreatie',],
            ['id' => 92, 'name' => 'Groen',],
            ['id' => 93, 'name' => 'Economieenondernemen',],
            ['id' => 94, 'name' => 'VerpleegkundeNOVAHaarlem',],
            ['id' => 95, 'name' => 'Godsdienst',],
            ['id' => 96, 'name' => 'Levensbeschouwelijkvormingsonderwijs',],
            ['id' => 97, 'name' => 'Overige',],
        ];
        $i = 0;
        foreach ($data as $record) {
            $baseSubject = new \tcCore\BaseSubject;
            $baseSubject->uuid = $baseSubject->resolveUuid();
            $baseSubject->id = $record['id'];
            $baseSubject->name = $record['name'];
            $baseSubject->show_in_onboarding = 1;
            $baseSubject->save();
        }
    }

    public function addUserRoles()
    {
        $data = [
            ['id' => 1, 'name' => 'Teacher',],
            ['id' => 2, 'name' => 'Invigilator',],
            ['id' => 3, 'name' => 'Student',],
            ['id' => 4, 'name' => 'Administrator',],
            ['id' => 5, 'name' => 'Account manager',],
            ['id' => 6, 'name' => 'School manager',],
            ['id' => 7, 'name' => 'School management',],
            ['id' => 8, 'name' => 'Mentor',],
            ['id' => 9, 'name' => 'Parent',],
            ['id' => 10, 'name' => 'Tech administrator',],
            ['id' => 11, 'name' => 'Support',],
            ['id' => 12, 'name' => 'Test team',],
        ];


        foreach ($data as $record) {
            $role = new \tcCore\Role;
            $role->id = $record['id'];
            $role->name = $record['name'];
            $role->save();
        }

    }

    public function addNationalItemBank()
    {

        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();

        if (User::where('username', 'info+ontwikkelaar@test-correct.nl')->exists()) {
            return;
        }

        $accountManager = FactoryUser::createAccountManager()->user;

        // maak een scholengemeenschap (table schools)
        $comprehensiveSchool = \tcCore\School::create([
            'customer_code'   => 'TBNI',
            'name'            => 'TLC Toetsenbakkerij vragencontent nationale itembank',
            'main_address'    => 'Agrobusinespark 10',
            'main_postal'     => '6708PV',
            'main_city'       => 'Wageningen',
            'main_country'    => 'Netherlands',
            'invoice_address' => 'alex please',
            'user_id'         => $accountManager->getKey(),
        ]);


        $locationA = \tcCore\SchoolLocation::create([
            "name"                                   => "TLC Toetsenbakkerij vragencontent nationale itembank",
            "customer_code"                          => "TBNI",
            "user_id"                                => $accountManager->getKey(),
            "school_id"                              => $comprehensiveSchool->getKey(),
            "grading_scale_id"                       => "1",
            "activated"                              => "1",
            "number_of_students"                     => "10",
            "number_of_teachers"                     => "10",
            "external_main_code"                     => "FF",
            "external_sub_code"                      => "00",
            "is_rtti_school_location"                => "0",
            "is_open_source_content_creator"         => "0",
            "is_allowed_to_view_open_source_content" => "0",
            "main_address"                           => "AgrobusinessPark 75",
            "invoice_address"                        => "AgrobusinessPark",
            "visit_address"                          => "AgrobusinessPark",
            "main_postal"                            => "6708PV",
            "invoice_postal"                         => "6708PV",
            "visit_postal"                           => "6708PV",
            "main_city"                              => "Wageningen",
            "invoice_city"                           => "Wageningen",
            "visit_city"                             => "Wageningen",
            "main_country"                           => "Netherlands",
            "invoice_country"                        => "Netherlands",
            "visit_country"                          => "Netherlands",
        ]);

        $section = \tcCore\Section::create([
            'name' => 'Nationale item bank sectie',
            'demo' => false,
        ]);

        $schoolLocationSection = \tcCore\SchoolLocationSection::create([
            'school_location_id' => $locationA->getKey(),
            'section_id'         => $section->getKey()
        ]);

        $teacher = FactoryUser::createTeacher($locationA)->user;


        Auth::loginUsingId($teacher->getKey());
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->setUser($teacher);
        //  dd(\tcCore\Http\Helpers\ActingAsHelper::getInstance()->getUser()->getKey());

        // add a schoolYear for the current year;
        $schoolYear = (new SchoolYear);
        $schoolYear->fill([
            'year'             => \Carbon\Carbon::now()->format('Y'),
            'school_locations' => [$locationA->getKey()],
        ]);
        $schoolYear->save();
        $schoolYear->delete();
        $schoolYear = $locationA->schoolYears()->first();

        $periodLocationA = (new Period());
        $periodLocationA->fill([
            'school_year_id'     => $schoolYear->getKey(),
            'name'               => 'TBNI Period',
            'school_location_id' => $locationA->getKey(),
            'start_date'         => \Carbon\Carbon::now()->subMonths(6),
            'end_date'           => \Carbon\Carbon::now()->addMonths(6),
        ]);
        $periodLocationA->save();

        $class = \tcCore\SchoolClass::create([
            'school_location_id'              => $locationA->getKey(),
            'school_year_id'                  => $schoolYear->getKey(),
            'name'                            => 'National item bank schoolclass',
            'education_level_id'              => 1,
            'education_level_year'            => 1,
            'is_main_school_class'            => 0,
            'do_not_overwrite_from_interface' => 0,
            'demo'                            => 0
        ]);

        $classB = \tcCore\SchoolClass::create([
            'school_location_id'              => $locationA->getKey(),
            'school_year_id'                  => $schoolYear->getKey(),
            'name'                            => 'National item bank schoolclass B',
            'education_level_id'              => 1,
            'education_level_year'            => 1,
            'is_main_school_class'            => 0,
            'do_not_overwrite_from_interface' => 0,
            'demo'                            => 0
        ]);
        // maak twee beheerders voor deze scholen;

        // maak twee docent
        $userFactory = new Factory(new User());
        $teacherA = $userFactory->generate([
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher National',
            'abbreviation'       => 'TN',
            'school_location_id' => $locationA->getKey(),
            'username'           => 'info+ontwikkelaar@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [1],
            'gender'             => 'Male',
        ]);

        $userFactory = new Factory(new User());
        $teacherB = $userFactory->generate([
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher National B',
            'abbreviation'       => 'TA',
            'school_location_id' => $locationA->getKey(),
            'username'           => 'info+ontwikkelaar-b@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [1],
            'gender'             => 'Male',
        ]);


        BaseSubject::/*where('name', 'NOT LIKE', '%CITO%')->*/ each(function ($baseSubject) use ($teacherA, $teacherB, $class, $classB, $section, $periodLocationA) {
            $subject = Subject::create(['name'            => $baseSubject->name,
                                        'section_id'      => $section->getKey(),
                                        'base_subject_id' => $baseSubject->id
            ]);
            $teacher = Teacher::create(['user_id'    => $teacherA->id,
                                        'subject_id' => $subject->id,
                                        'class_id'   => $class->id
            ]);
            $teacher = Teacher::create(['user_id'    => $teacherB->id,
                                        'subject_id' => $subject->id,
                                        'class_id'   => $classB->id
            ]);

            $test = FactoryScenarioTestTestWithAllQuestionTypes::create("TBNI $subject->name", $teacherA)->getTestModel();
            $test->fill([
                'subject_id'             => $subject->id,
                'education_level_id'     => 1,
                'period_id'              => $periodLocationA->id,
                'abbreviation'           => 'LDT', //not finished: NOT_LDT, if finished: LDT
                'introduction'           => 'Beste docent,

                                   Dit is de test toets voor de nationale item bank.',
                'is_open_source_content' => false,
                'demo'                   => false,
                'scope'                  => 'ldt', //not finished: not_ldt, if finished: ldt
            ]);
            $test->testQuestions->each(function ($testQuestion) {
                $question = $testQuestion->question->getQuestionInstance();
                $question->setAttribute('question', "TBNI: $question->question");
                $question->save();
            });
            Auth::login($teacherA);
            $test->save();
        });
    }

    private function addEducationLevels()
    {
        $data = [
//            ['id'=> 0, 'name' => 'uwlr_education_level','max_years' => 8,'attainment_education_level_id' => 0,'min_attainment_year' => NULL],
            ['id' => 1, 'name' => 'VWO', 'max_years' => 6, 'attainment_education_level_id' => 1, 'min_attainment_year' => 4],
            ['id' => 2, 'name' => 'Gymnasium', 'max_years' => 6, 'attainment_education_level_id' => 2, 'min_attainment_year' => 4],
            ['id' => 3, 'name' => 'Havo', 'max_years' => 6, 'attainment_education_level_id' => 3, 'min_attainment_year' => 4],
            ['id' => 4, 'name' => 'Mavo / Vmbo tl', 'max_years' => 5, 'attainment_education_level_id' => 4, 'min_attainment_year' => 3],
            ['id' => 5, 'name' => 'Vmbo gl', 'max_years' => 4, 'attainment_education_level_id' => 5, 'min_attainment_year' => 3],
            ['id' => 6, 'name' => 'Vmbo kb', 'max_years' => 4, 'attainment_education_level_id' => 6, 'min_attainment_year' => 3],
            ['id' => 7, 'name' => 'Vmbo bb', 'max_years' => 4, 'attainment_education_level_id' => 7, 'min_attainment_year' => 3],
            ['id' => 8, 'name' => 'Lwoo', 'max_years' => 4, 'attainment_education_level_id' => 8, 'min_attainment_year' => NULL],
            ['id' => 9, 'name' => 'Atheneum', 'max_years' => 4, 'attainment_education_level_id' => 9, 'min_attainment_year' => 4],
            ['id' => 10, 'name' => 'Mavo/Havo', 'max_years' => 2, 'attainment_education_level_id' => 4, 'min_attainment_year' => 2],
            ['id' => 11, 'name' => 'Havo/VWO', 'max_years' => 3, 'attainment_education_level_id' => 3, 'min_attainment_year' => 3],
            ['id' => 12, 'name' => 't/h', 'max_years' => 4, 'attainment_education_level_id' => 12, 'min_attainment_year' => NULL],
            ['id' => 13, 'name' => 'h/v', 'max_years' => 6, 'attainment_education_level_id' => 13, 'min_attainment_year' => NULL],
            ['id' => 14, 'name' => 'MBO-N1', 'max_years' => 4, 'attainment_education_level_id' => 14, 'min_attainment_year' => NULL],
            ['id' => 15, 'name' => 'MBO-N2', 'max_years' => 4, 'attainment_education_level_id' => 15, 'min_attainment_year' => NULL],
            ['id' => 16, 'name' => 'MBO-N3', 'max_years' => 4, 'attainment_education_level_id' => 16, 'min_attainment_year' => NULL],
            ['id' => 17, 'name' => 'MBO-N4', 'max_years' => 4, 'attainment_education_level_id' => 17, 'min_attainment_year' => NULL],
            ['id' => 18, 'name' => 'HBO Bachelor', 'max_years' => 4, 'attainment_education_level_id' => 18, 'min_attainment_year' => NULL],
            ['id' => 21, 'name' => 'HBO Master', 'max_years' => 2, 'attainment_education_level_id' => 21, 'min_attainment_year' => NULL],
            ['id' => 24, 'name' => 'WO Bachelor', 'max_years' => 3, 'attainment_education_level_id' => 24, 'min_attainment_year' => NULL],
            ['id' => 27, 'name' => 'WO Master', 'max_years' => 2, 'attainment_education_level_id' => 27, 'min_attainment_year' => NULL],
            ['id' => 30, 'name' => 'Demo', 'max_years' => 1, 'attainment_education_level_id' => 30, 'min_attainment_year' => NULL],
            ['id' => 33, 'name' => 'Groep', 'max_years' => 9, 'attainment_education_level_id' => 33, 'min_attainment_year' => NULL],
        ];

        foreach ($data as $record) {
            $role = new \tcCore\EducationLevel();
            $role->id = $record['id'];
            $role->name = $record['name'];
            $role->max_years = $record['max_years'];
            $role->attainment_education_level_id = $record['attainment_education_level_id'];
            $role->min_attainment_year = $record['min_attainment_year'];
            $role->save();
        }
    }

    private function addTestTakeStatuses()
    {
        $data = [
            ['id' => 1, 'name' => 'Planned', 'is_individual_status' => 0],
            ['id' => 2, 'name' => 'Test not taken', 'is_individual_status' => 1],
            ['id' => 3, 'name' => 'Taking test', 'is_individual_status' => 0],
            ['id' => 4, 'name' => 'Handed in', 'is_individual_status' => 1],
            ['id' => 5, 'name' => 'Taken away', 'is_individual_status' => 1],
            ['id' => 6, 'name' => 'Taken', 'is_individual_status' => 0],
            ['id' => 7, 'name' => 'Discussing', 'is_individual_status' => 0],
            ['id' => 8, 'name' => 'Discussed', 'is_individual_status' => 0],
            ['id' => 9, 'name' => 'Rated', 'is_individual_status' => 0],
        ];

        foreach ($data as $record) {
            $role = new \tcCore\TestTakeStatus();
            $role->id = $record['id'];
            $role->name = $record['name'];
            $role->is_individual_status = $record['is_individual_status'];
            $role->save();
        }
    }

    private function addTestTakeEventTypes()
    {
        $data = [
            ['id' => 1, 'name' => 'Start', 'requires_confirming' => 0, 'reason' => 'start-test', 'show_alarm_to_student' => 0],
            ['id' => 2, 'name' => 'Stop', 'requires_confirming' => 0, 'reason' => 'stop-test', 'show_alarm_to_student' => 0],
            ['id' => 3, 'name' => 'Lost focus', 'requires_confirming' => 1, 'reason' => 'lost-focus', 'show_alarm_to_student' => 1],
            ['id' => 4, 'name' => 'Screenshot', 'requires_confirming' => 1, 'reason' => 'screenshot', 'show_alarm_to_student' => 1],
            ['id' => 5, 'name' => 'Started late', 'requires_confirming' => 1, 'reason' => 'started-late', 'show_alarm_to_student' => 1],
            ['id' => 6, 'name' => 'Start discussion', 'requires_confirming' => 0, 'reason' => 'start-discussion', 'show_alarm_to_student' => 0],
            ['id' => 7, 'name' => 'End discussion', 'requires_confirming' => 0, 'reason' => 'end-discussion', 'show_alarm_to_student' => 0],
            ['id' => 8, 'name' => 'Continue', 'requires_confirming' => 0, 'reason' => 'continue', 'show_alarm_to_student' => 0],
            ['id' => 9, 'name' => 'Application closed', 'requires_confirming' => 1, 'reason' => 'application-closed', 'show_alarm_to_student' => 1],
            ['id' => 10, 'name' => 'Lost focus alt tab', 'requires_confirming' => 1, 'reason' => 'alt-tab', 'show_alarm_to_student' => 1],
            ['id' => 11, 'name' => 'Pressed meta key', 'requires_confirming' => 1, 'reason' => 'before-input-meta', 'show_alarm_to_student' => 1],
            ['id' => 12, 'name' => 'Pressed alt key', 'requires_confirming' => 1, 'reason' => 'before-input-alt', 'show_alarm_to_student' => 1],
            ['id' => 13, 'name' => 'Application closed alt+f4', 'requires_confirming' => 1, 'reason' => 'alt+f4', 'show_alarm_to_student' => 1],
            ['id' => 14, 'name' => 'Lost focus blur', 'requires_confirming' => 1, 'reason' => 'blur', 'show_alarm_to_student' => 1],
            ['id' => 15, 'name' => 'Window hidden', 'requires_confirming' => 1, 'reason' => 'hide', 'show_alarm_to_student' => 1],
            ['id' => 16, 'name' => 'Window minimized', 'requires_confirming' => 1, 'reason' => 'minimize', 'show_alarm_to_student' => 1],
            ['id' => 17, 'name' => 'Window moved', 'requires_confirming' => 1, 'reason' => 'move', 'show_alarm_to_student' => 1],
            ['id' => 18, 'name' => 'Window not fullscreen', 'requires_confirming' => 1, 'reason' => 'leave-full-screen', 'show_alarm_to_student' => 1],
            ['id' => 19, 'name' => 'Always on top changed', 'requires_confirming' => 1, 'reason' => 'always-on-top-changed', 'show_alarm_to_student' => 1],
            ['id' => 20, 'name' => 'Window resized', 'requires_confirming' => 1, 'reason' => 'resize', 'show_alarm_to_student' => 1],
            ['id' => 21, 'name' => 'Force shutdown', 'requires_confirming' => 1, 'reason' => 'session-end', 'show_alarm_to_student' => 1],
            ['id' => 22, 'name' => 'Screenshot', 'requires_confirming' => 1, 'reason' => 'printscreen', 'show_alarm_to_student' => 1],
            ['id' => 23, 'name' => 'Other window on top', 'requires_confirming' => 1, 'reason' => 'other-window-on-top', 'show_alarm_to_student' => 1],
            ['id' => 24, 'name' => 'Used unallowed Ctrl key combination', 'requires_confirming' => 1, 'reason' => 'ctrl-key', 'show_alarm_to_student' => 1],
            ['id' => 25, 'name' => 'Illegal programs', 'requires_confirming' => 1, 'reason' => 'illegal-programs', 'show_alarm_to_student' => 1],
            ['id' => 26, 'name' => 'Rejoined', 'requires_confirming' => 1, 'reason' => 'rejoined', 'show_alarm_to_student' => 1],
            ['id' => 27, 'name' => 'Forbidden device', 'requires_confirming' => 1, 'reason' => 'hid', 'show_alarm_to_student' => 1],
            ['id' => 28, 'name' => 'VM detected', 'requires_confirming' => 1, 'reason' => 'vm', 'show_alarm_to_student' => 0],
        ];
        foreach ($data as $record) {
            $testTakeEventType = new \tcCore\TestTakeEventType();
            $testTakeEventType->id = $record['id'];
            $testTakeEventType->name = $record['name'];
            $testTakeEventType->requires_confirming = $record['requires_confirming'];
            $testTakeEventType->reason = $record['reason'];
            $testTakeEventType->show_alarm_to_student = $record['show_alarm_to_student'];
            $testTakeEventType->save();
        }
    }


}
