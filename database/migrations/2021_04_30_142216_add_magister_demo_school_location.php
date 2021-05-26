<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMagisterDemoSchoolLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (self::notProduction()) {

            $location = \tcCore\SchoolLocation::create([
                "name"                                   => "Magister schoollocatie",
                "customer_code"                          => "Magister Schoollocation",
                "user_id"                                => 520,
                "school_id"                              => tcCore\School::first()->getKey(),
                "grading_scale_id"                       => "1",
                "activated"                              => "1",
                "number_of_students"                     => "10",
                "number_of_teachers"                     => "10",
                "external_main_code"                     => "99DE",
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
                'name' => 'Magister sectie',
                'demo' => false,
            ]);

            $schoolLocationSection = \tcCore\SchoolLocationSection::create([
                'school_location_id' => $location->getKey(),
                'section_id'         => $section->getKey()
            ]);

            Auth::loginUsingId(1486);
            \tcCore\Http\Helpers\ActingAsHelper::getInstance()->setUser(tcCore\User::find(1486));
            //  dd(\tcCore\Http\Helpers\ActingAsHelper::getInstance()->getUser()->getKey());

            // add a schoolYear for the current year;
            $schoolYear = (new tcCore\SchoolYear);
            $schoolYear->fill([
                'year'             => '2018',
                'school_locations' => [$location->getKey()]
            ]);
            $schoolYear->save();

            $periodLocation = (new tcCore\Period());
            $periodLocation->fill([
                'school_year_id'     => $schoolYear->getKey(),
                'name'               => 'huidige voor MS A',
                'school_location_id' => $location->getKey(),
                'start_date'         => \Carbon\Carbon::now()->subMonths(6),
                'end_date'           => \Carbon\Carbon::now()->addMonths(6),
            ]);
            $periodLocation->save();

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schoolLocation = tcCore\SchoolLocation::where('external_main_code', '99DE')
            ->where('external_sub_code', '00')
            ->first();
        if ($schoolLocation) {
            $slSection = tcCore\SchoolLocationSection::where('school_location_id', $schoolLocation->getKey());
            Section::where('name', 'Magister sectie')->where('id', $slSection->section_id)->forceDelete();
            $slSection->forceDelete();
            $schoolLocation->forceDelete();
        }
    }

    public static function notProduction()
    {
        return str_contains('testportal', config('app.url_login') && str_contains('.test', config('app.url_login')));
    }

}
