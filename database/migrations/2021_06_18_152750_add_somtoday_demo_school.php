<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use tcCore\Lib\User\Factory;
use tcCore\Subject;
use tcCore\User;

class AddSomtodayDemoSchool extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        if (\tcCore\Http\Helpers\BaseHelper::notProduction()) {
//            $location = \tcCore\SchoolLocation::create([
//                "name" => "SOMToday schoollocatie",
//                "customer_code" => "OV",
//                "user_id" => 520,
//                "school_id" => tcCore\School::first()->getKey(),
//                "grading_scale_id" => "1",
//                "activated" => "1",
//                "number_of_students" => "10",
//                "number_of_teachers" => "10",
//                "external_main_code" => "06SS",
//                "external_sub_code" => "00",
//                "is_rtti_school_location" => "0",
//                "is_open_source_content_creator" => "0",
//                "is_allowed_to_view_open_source_content" => "0",
//                "main_address" => "AgrobusinessPark 75",
//                "invoice_address" => "AgrobusinessPark",
//                "visit_address" => "AgrobusinessPark",
//                "main_postal" => "6708PV",
//                "invoice_postal" => "6708PV",
//                "visit_postal" => "6708PV",
//                "main_city" => "Wageningen",
//                "invoice_city" => "Wageningen",
//                "visit_city" => "Wageningen",
//                "main_country" => "Netherlands",
//                "invoice_country" => "Netherlands",
//                "visit_country" => "Netherlands",
//                "lvs_active" => true,
//                "lvs_type" => \tcCore\SchoolLocation::LVS_SOMTODAY,
//            ]);
//
//            $section = \tcCore\Section::create([
//                'name' => \tcCore\Http\Helpers\ImportHelper::DUMMY_SECTION_NAME,
//                'demo' => false,
//            ]);
//
//            $schoolLocationSection = \tcCore\SchoolLocationSection::create([
//                'school_location_id' => $location->getKey(),
//                'section_id' => $section->getKey()
//            ]);
//
//            $userFactory = new Factory(new User());
//            $user = $userFactory->generate([
//                'name_first' => 'somtoday user',
//                'name' => 'somtoday user',
//                'external_id' => 'somtoday user',
//                'username' => 'somtoday user',
//                'password' => '',
//                'user_roles' => [3],
//                'school_location_id' => $location->getKey(),
//                'send_welcome_email' => 1,
//            ]);
//
//            Auth::loginUsingId($user->getKey());
//            \tcCore\Http\Helpers\ActingAsHelper::getInstance()->setUser($user);
//            //  dd(\tcCore\Http\Helpers\ActingAsHelper::getInstance()->getUser()->getKey());
//
//            // add a schoolYear for the current year;
//            $schoolYear = (new tcCore\SchoolYear);
//            $schoolYear->fill([
//                'year' => '2019',
//                'school_locations' => [$location->getKey()]
//            ]);
//            $schoolYear->save();
//
//            $periodLocation = (new tcCore\Period());
//            $periodLocation->fill([
//                'school_year_id' => $schoolYear->getKey(),
//                'name' => 'huidige voor Somtoday A',
//                'school_location_id' => $location->getKey(),
//                'start_date' => \Carbon\Carbon::now()->subMonths(6),
//                'end_date' => \Carbon\Carbon::now()->addMonths(6),
//            ]);
//
//
//            $periodLocation->save();
//            $user->forceDelete();
//
//            // add educationlevels.
//            foreach ([1, 2, 3, 4] as $level) {
//                \tcCore\SchoolLocationEducationLevel::create(
//                    [
//                        'school_location_id' => $location->getKey(),
//                        'education_level_id' => $level,
//                    ]
//                );
//            }
//
//            $baseSubjects = \tcCore\BaseSubject::where('id', '<', 5)->get();
//
//            foreach ($baseSubjects as $baseSubject) {
//                Subject::create([
//                    'name'            => $baseSubject->name,
//                    'base_subject_id' => $baseSubject->id,
//                    'abbreviation'    => strtoupper(substr($baseSubject->name, 0, 3)),
//                    'section_id'      => $section->id,
//                ]);
//            }
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
