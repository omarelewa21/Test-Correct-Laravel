<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\User;

class SwitchSchoolAccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();


        // maak een scholengemeenschap (table schools)
        $comprehensiveSchool = \tcCore\School::create([
            'customer_code'   => 'MS',
            'name'            => 'MultiSchool Scholengemeenschap',
            'main_address'    => 'Agrobusinespark 10',
            'main_postal'     => '6708PV',
            'main_city'       => 'Wageningen',
            'main_country'    => 'Netherlands',
            'invoice_address' => 'alex please',
            'user_id'         => 520,
        ]);// maak twee scholen voor deze scholengemeenschap (table school_locations)
        ;

        $locationA = \tcCore\SchoolLocation::create([
            "name"                                   => "MS A",
            "customer_code"                          => "MSA",
            "user_id"                                => 755,
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

        $locationB = \tcCore\SchoolLocation::create([
            "name"                                   => "MSB",
            "customer_code"                          => "MSB",
            "user_id"                                => 755,
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
        $userFactory = new Factory(new User());
        $adminA = $userFactory->generate([
            'name_first'         => 'Admin',
            'name_suffix'        => '',
            'name'               => 'Admin A',
            'abbreviation'       => 'AA',
            'school_location_id' => $locationA->getKey(),
            'username'           => 'admin-a@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [6],
            'gender'             => 'Male',
        ]);

        Auth::loginUsingId(1486);
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->setUser(User::find(1486));
        //  dd(\tcCore\Http\Helpers\ActingAsHelper::getInstance()->getUser()->getKey());

        // add a schoolYear for the current year;
        $schoolYear = (new SchoolYear);
        $schoolYear->fill([
            'year'             => '2020',
            'school_locations' => [$locationA->getKey(), $locationB->getKey()],
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

        $periodLocationB = (new Period());
        $periodLocationB->fill([
            'school_year_id'     => $schoolYear->getKey(),
            'name'               => 'huidige voor MS B',
            'school_location_id' => $locationB->getKey(),
            'start_date'         => \Carbon\Carbon::now()->subMonths(6),
            'end_date'           => \Carbon\Carbon::now()->addMonths(6),
        ]);
        $periodLocationB->save();

        // maak twee beheerders voor deze scholen;


        $locationA->user()->associate($adminA);
        $locationA->save();

        $userFactory = new Factory(new User());
        $adminB = $userFactory->generate([
            'name_first'         => 'Admin',
            'name_suffix'        => '',
            'name'               => 'Admin B',
            'abbreviation'       => 'BB',
            'school_location_id' => $locationB->getKey(),
            'username'           => 'admin-b@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [6],
            'gender'             => 'Male',
        ]);


        // maak twee docent
        $userFactory = new Factory(new User());
        $teacherA = $userFactory->generate([
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher A',
            'abbreviation'       => 'TA',
            'school_location_id' => $locationB->getKey(),
            'username'           => 'teacher-a@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [1],
            'gender'             => 'Male',
            'external_id'        => 'teacher-a-external-id',
        ]);

        $teacherA->addSchoolLocation($locationA);

        $userFactory = new Factory(new User());
        $teacherB = $userFactory->generate([
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher B',
            'abbreviation'       => 'TB',
            'school_location_id' => $locationB->getKey(),
            'username'           => 'teacher-b@test-correct.nl',
            'password'           => 'Sobit4456',
            'user_roles'         => [1],
            'gender'             => 'Male',
        ]);

        collect(['c', 'd', 'e', 'f', 'g', 'h', 'k', 'l'])->each(function ($letter) use (
            $locationB,
            $comprehensiveSchool
        ) {
            $userFactory = new Factory(new User());
            $userFactory->generate([
                'name_first'         => 'Teacher',
                'name_suffix'        => '',
                'name'               => sprintf('Teacher %s', strtoupper($letter)),
                'abbreviation'       => sprintf('T%s', strtoupper($letter)),
                'school_location_id' => $locationB->getKey(),
                'username'           => sprintf('teacher-%s@test-correct.nl', $letter),
                'password'           => 'Sobit4456',
                'user_roles'         => [1],
                'gender'             => 'Male',
            ]);
        });


        //
    }

}
