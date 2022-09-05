<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\User;
use tcCore\BaseSubject;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\SchoolLocation;
use tcCore\Test;


class CitoAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();

        if(User::where('username', 'teacher-cito@test-correct.nl')->exists()){
            return;
        }

        $this->generateCitoSchoolWithTests();
        return;

        // maak een scholengemeenschap (table schools)
        $comprehensiveSchool = \tcCore\School::create([
            'customer_code'   => 'CITO-TOETSENOPMAAT',
            'name'            => 'Cito Scholengemeenschap',
            'main_address'    => 'Agrobusinespark 10',
            'main_postal'     => '6708PV',
            'main_city'       => 'Wageningen',
            'main_country'    => 'Netherlands',
            'invoice_address' => 'alex please',
            'user_id'         => 520,
        ]);



        $locationA = \tcCore\SchoolLocation::create([
            "name"                                   => "Cito schoollocatie",
            "customer_code"                          => "CITO-TOETSENOPMAAT",
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
        	'name' => 'Cito sectie',
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
            'year'             => '2020',
            'school_locations' => [$locationA->getKey()],
        ]);
        $schoolYear->save();

        $periodLocationA = (new Period());
        $periodLocationA->fill([
            'school_year_id'     => $schoolYear->getKey(),
            'name'               => 'huidige voor MS A',
            'school_location_id' => $locationA->getKey(),
            'start_date'         => \Carbon\Carbon::now()->subMonths(6),
            'end_date'           => \Carbon\Carbon::now()->addMonths(6),
        ]);
        $periodLocationA->save();

		$class = \tcCore\SchoolClass::create([
			'school_location_id' => $locationA->getKey(),
	        'school_year_id' => $schoolYear->getKey(),
	        'name' => 'Cito schoolclass',
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
            'name'               => 'Teacher CITO',
            'abbreviation'       => 'TA',
            'school_location_id' => $locationA->getKey(),
            'username'           => 'teacher-cito@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [1],
            'gender'             => 'Male',
        ]);
        $locationB = SchoolLocation::where('customer_code','standaard testschool')->first();

        // add a schoolYear for the current year;
        $schoolYearB = (new SchoolYear);
        $schoolYearB->fill([
            'year'             => '2020',
            'school_locations' => [$locationB->getKey()],
        ]);
        $schoolYearB->save();

        $periodLocationB = (new Period());
        $periodLocationB->fill([
            'school_year_id'     => $schoolYear->getKey(),
            'name'               => 'huidige voor MS A',
            'school_location_id' => $locationA->getKey(),
            'start_date'         => \Carbon\Carbon::now()->subMonths(6),
            'end_date'           => \Carbon\Carbon::now()->addMonths(6),
        ]);
        $periodLocationB->save();

        $classB = \tcCore\SchoolClass::create([
            'school_location_id' => $locationB->getKey(),
            'school_year_id' => $schoolYearB->getKey(),
            'name' => 'schoolclass for Cito testing',
            'education_level_id' => 1,
            'education_level_year' => 1,
            'is_main_school_class' => 0,
            'do_not_overwrite_from_interface' => 0,
            'demo' => 0
        ]);

        $userFactory = new Factory(new User());
        $teacherB = $userFactory->generate([
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher CITO B',
            'abbreviation'       => 'TA',
            'school_location_id' => $locationB->getKey(),
            'username'           => 'teacher-cito-b@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [1],
            'gender'             => 'Male',
        ]);
        
//		collect([
//			'CITO-Economie',
//			'CITO-Natuurkunde',
//			'CITO-WiskundeA',
//			'CITO-Nask1',
//			'CITO-Nask2',
//			'CITO-Aardrijkskunde',
//			'CITO-Biologie',
//			'CITO-Geschiedenis',
//			'CITO-Scheikunde',
//			'CITO-Wiskunde',
//			'CITO-WiskundeB',
//		])->each(function($subjectName) {
//			BaseSubject::create(['name'=>$subjectName]);
//		});
		BaseSubject::all()->each(function($baseSubject) use($teacherA,$teacherB,$class,$classB,$periodLocationB, $section) {
			$subject = Subject::create([	'name'=>$baseSubject->name,
								'section_id'=> $section->getKey(),
								'base_subject_id'=>$baseSubject->id	
						]);
			$teacher = Teacher::create([	'user_id'=>$teacherA->id,
											'subject_id'=>$subject->id,
											'class_id'=>$class->id
										]);
			$teacher = Teacher::create([	'user_id'=>$teacherB->id,
											'subject_id'=>$subject->id,
											'class_id'=>$classB->id
										]);
		
            $test = new Test([
                                  'subject_id'=>$subject->id,
                                  'education_level_id'=>1,
                                  'period_id'=>$periodLocationB->id,
                                  'test_kind_id'=>3,
                                  'name'=>'test cito '.$subject->name,
                                  'abbreviation'=>'CITO',
                                  'education_level_year'=>1,
                                  'status'=>1,
                                  'introduction'=>'Beste docent,

Dit is de test toets voor de cito.',
                                  'shuffle'=>false,
                                  'is_system_test'=>false,
                                  'question_count'=>0,
                                  'is_open_source_content'=>false,
                                  'demo'=>false,
                                  'scope'=>'cito',
                                  'published'=>'1',
                            ]);
            $test->setAttribute('author_id', $teacherB->id);
            $test->setAttribute('owner_id', $teacherB->school_location_id);
            $test->save();
        });


        
    }

    public function generateCitoSchoolWithTests()
    {
        $factoryScenarioSchool = \tcCore\FactoryScenarios\FactoryScenarioSchoolCito::create();
        $school_location = $factoryScenarioSchool->school->schoolLocations()->where('customer_code', 'CITO-TOETSENOPMAAT')->first();

        $primaryTestAuthor = $school_location->users()->where('username', 'teacher-cito@test-correct.nl')->first();
        $secondaryTestAuthor = $school_location->users()->where('username', 'teacher-cito-b@test-correct.nl')->first();

        $collection = $school_location->schoolLocationSections->where('demo', false)->first()->section->subjects->split(2);

        $firstHalf = $collection[0];
        $secondHalf = $collection[1];

        $firstHalf->each(function ($subject) use ($primaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($primaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'CITO',
                    'scope'              => 'cito',
                    'education_level_id' => '1',
                ])
                ->addQuestions([
                    \tcCore\Factories\Questions\FactoryQuestionOpenShort::create(),
                ]);
        });
        $secondHalf->each(function ($subject) use ($secondaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($secondaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'CITO',
                    'scope'              => 'cito',
                    'education_level_id' => '1',
                ])
                ->addQuestions([
                    \tcCore\Factories\Questions\FactoryQuestionOpenShort::create(),
                ]);
        });

    }

}
