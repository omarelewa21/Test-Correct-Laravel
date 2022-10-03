<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use tcCore\BaseSubject;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithAllQuestionTypes;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class NationalItemBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();

        if(User::where('username', 'info+ontwikkelaar@test-correct.nl')->exists()){
            return;
        }

        // maak een scholengemeenschap (table schools)
        $comprehensiveSchool = \tcCore\School::create([
            'customer_code'   => 'TBNI',
            'name'            => 'TLC Toetsenbakkerij vragencontent nationale itembank',
            'main_address'    => 'Agrobusinespark 10',
            'main_postal'     => '6708PV',
            'main_city'       => 'Wageningen',
            'main_country'    => 'Netherlands',
            'invoice_address' => 'alex please',
            'user_id'         => 520,
        ]);



        $locationA = \tcCore\SchoolLocation::create([
            "name"                                   => "TLC Toetsenbakkerij vragencontent nationale itembank",
            "customer_code"                          => "TBNI",
            "user_id"                                => 520,
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
            'section_id' => $section->getKey()
        ]);


        Auth::loginUsingId(1486);
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->setUser(User::find(1486));
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
            'school_location_id' => $locationA->getKey(),
            'school_year_id' => $schoolYear->getKey(),
            'name' => 'National item bank schoolclass',
            'education_level_id' => 1,
            'education_level_year' => 1,
            'is_main_school_class' => 0,
            'do_not_overwrite_from_interface' => 0,
            'demo' => 0
        ]);

        $classB = \tcCore\SchoolClass::create([
            'school_location_id' => $locationA->getKey(),
            'school_year_id' => $schoolYear->getKey(),
            'name' => 'National item bank schoolclass B',
            'education_level_id' => 1,
            'education_level_year' => 1,
            'is_main_school_class' => 0,
            'do_not_overwrite_from_interface' => 0,
            'demo' => 0
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


        BaseSubject::/*where('name', 'NOT LIKE', '%CITO%')->*/each(function ($baseSubject) use ($teacherA, $teacherB, $class, $classB, $section, $periodLocationA) {
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
}
