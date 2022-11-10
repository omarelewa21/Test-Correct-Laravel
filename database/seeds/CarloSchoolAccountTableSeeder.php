<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\User;

class CarloSchoolAccountTableSeeder extends Seeder
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
            'customer_code'   => 'K999K990',
            'name'            => 'K999 en K990 gemeenschap',
            'main_address'    => 'Agrobusinespark 10',
            'main_postal'     => '6708PV',
            'main_city'       => 'Wageningen',
            'main_country'    => 'Netherlands',
            'invoice_address' => 'alex please',
            'count_active_licenses'  =>'1112',
            'count_active_teachers'  =>'8',
            'count_expired_licenses' =>'2',
            'count_licenses' =>'1',
            'count_questions'    =>'1',
            'count_students' =>'1',
            'count_teachers' =>'1',
            'count_tests'    =>'1',
            'count_tests_taken'  =>'1',
            'external_main_code' =>'XXXX',
            'count_text2speech'  =>'2',
            'uuid'   =>Str::uuid(),

            'user_id'         => 521,
        ]);// maak twee scholen voor deze scholengemeenschap (table school_locations)
        ;

        $locationA = \tcCore\SchoolLocation::create([
            "name"                                   => "K999 schoollocatie",
            "customer_code"                          => "K999",
            "user_id"                                => 521,
            "school_id"                              => $comprehensiveSchool->getKey(),
            "grading_scale_id"                       => "1",
            "activated"                              => "1",
            "number_of_students"                     => "10",
            "number_of_teachers"                     => "10",
            "external_main_code"                     => "K999",
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
            "count_text2speech"                      => '12',
            "edu_ix_organisation_id"                 => '',
            "uuid"                                   =>  Str::uuid(),
            "allow_inbrowser_testing"                => '1',
            "allow_new_player_access"                => '2',
            "intense"                                => '',
            "school_language"                        => 'nl',
            "lvs_type"                               => 'Magister',
            "lvs_active"                             => '0',
            "sso"                                    => '0',
            "sso_type"                               => 'Entreefederatie',
            "sso_active"                             => '1',
            "lvs_authorization_key"                  => '',
            "lvs_client_name"                        => '',
            "lvs_client_code"                        => '',
            "lvs_active_no_mail_allowed"             => '1',
            "accepted_mail_domain"                   => '@pbascholengemeenschap.nl',
            "no_mail_request_detected"               => '2021-10-13 16:37:34',
            "allow_guest_accounts"                   => '1',
            "company_id"                             => 'HUBSPOTK999',
            "allow_new_student_environment"          => '1',
        ]);

        $locationB = \tcCore\SchoolLocation::create([
            "name"                                   => "K990",
            "customer_code"                          => "K990",
            "user_id"                                => 521,
            "school_id"                              => $comprehensiveSchool->getKey(),
            "grading_scale_id"                       => "1",
            "activated"                              => "1",
            "number_of_students"                     => "10",
            "number_of_teachers"                     => "10",
            "external_main_code"                     => "K990",
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
            "count_text2speech"                      => '12',
            "edu_ix_organisation_id"                 => '',
            "uuid"                                   =>  Str::uuid(),
            "allow_inbrowser_testing"                => '0',
            "allow_new_player_access"                => '2',
            "intense"                                => '',
            "school_language"                        => 'nl',
            "lvs_type"                               => 'Magister',
            "lvs_active"                             => '0',
            "sso"                                    => '0',
            "sso_type"                               => 'Entreefederatie',
            "sso_active"                             => '1',
            "lvs_authorization_key"                  => '',
            "lvs_client_name"                        => '',
            "lvs_client_code"                        => '',
            "lvs_active_no_mail_allowed"             => '1',
            "accepted_mail_domain"                   => '',
            "no_mail_request_detected"               => '',
            "allow_guest_accounts"                   => '1',
            "company_id"                             => '',
            "allow_new_student_environment"          => '1',
        ]);
        $userFactory = new Factory(new User());
        $adminA = $userFactory->generate([
            'name_first'         => 'Schoolbeheerder',
            'name_suffix'        => '',
            'name'               => 'K999',
            'abbreviation'       => 'AA',
            'school_location_id' => $locationA->getKey(),
            'username'           => 'carloschoep+K999schoolbeheerder@hotmail.com',
            'password'           => 'TCSoBit500',
            'user_roles'         => [6],
            'gender'             => 'Male',
        ]);

        Auth::loginUsingId(1486);
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->setUser(User::find(1486));
        //  dd(\tcCore\Http\Helpers\ActingAsHelper::getInstance()->getUser()->getKey());

        // add a schoolYear for the current year;
        $schoolYear = (new SchoolYear);
        $schoolYear->fill([
            'year'             => Carbon::now()->format('Y'),
            'school_locations' => [$locationA->getKey(), $locationB->getKey()],
        ]);
        $schoolYear->save();

        $periodLocationA = (new Period());
        $periodLocationA->fill([
            'school_year_id'     => $schoolYear->getKey(),
            'name'               => 'huidige voor K999',
            'school_location_id' => $locationA->getKey(),
            'start_date'         => Carbon::now()->subMonths(6),
            'end_date'           => Carbon::now()->addMonths(6),
        ]);
        $periodLocationA->save();

        $periodLocationB = (new Period());
        $periodLocationB->fill([
            'school_year_id'     => $schoolYear->getKey(),
            'name'               => 'huidige voor K990',
            'school_location_id' => $locationB->getKey(),
            'start_date'         => \Carbon\Carbon::now()->subMonths(6),
            'end_date'           => \Carbon\Carbon::now()->addMonths(6),
        ]);
        $periodLocationB->save();

        $locationA->user()->associate($adminA);
        $locationA->save();

        $userFactory = new Factory(new User());
        $adminB = $userFactory->generate([
            'name_first'         => 'Schoolbeheerder',
            'name_suffix'        => '',
            'name'               => 'K990',
            'abbreviation'       => 'BB',
            'school_location_id' => $locationB->getKey(),
            'username'           => 'carloschoep+K990schoolbeheerder@hotmail.com',
            'password'           => 'TCSoBit500',
            'user_roles'         => [6],
            'gender'             => 'Male',
        ]);

        $userFactory = new Factory(new User());
        $teacherA = $userFactory->generate([
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher K999',
            'abbreviation'       => 'TA',
            'school_location_id' => $locationA->getKey(),
            'username'           => 'carloschoep+K999docent@hotmail.com',
            'password'           => 'TCSoBit500',
            'user_roles'         => [1],
            'gender'             => 'Male',
            'external_id'        => 'teacher-14-K999-external-id',
        ]);

        $teacherA->addSchoolLocation($locationA);

        $userFactory = new Factory(new User());
        $teacherB = $userFactory->generate([
            'name_first'         => 'Teacher',
            'name_suffix'        => '',
            'name'               => 'Teacher K990',
            'abbreviation'       => 'TB',
            'school_location_id' => $locationB->getKey(),
            'username'           => 'carloschoep+K990docent@hotmail.com',
            'password'           => 'TCSoBit500',
            'user_roles'         => [1],
            'gender'             => 'Male',
        ]);

        $teacherB->addSchoolLocation($locationB);

    }

}
