<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\Subject;

class AddEducationLevelsAndSubjectsToMagisterDemoSchoolLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        if (\tcCore\Http\Helpers\BaseHelper::notProduction()) {
//            $location = \tcCore\SchoolLocation::firstWhere([
//                "name"          => "Magister schoollocatie",
//                "customer_code" => "Magister Schoollocation",
//            ]);
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
//            $magisterSection = tcCore\Section::firstwhere(['name' => \tcCore\Http\Helpers\ImportHelper::DUMMY_SECTION_NAME]);
//            $baseSubjects = \tcCore\BaseSubject::where('id', '<', 5)->get();
//
//            foreach ($baseSubjects as $baseSubject) {
//                Subject::create([
//                    'name'            => $baseSubject->name,
//                    'base_subject_id' => $baseSubject->id,
//                    'abbreviation'    => strtoupper(substr($baseSubject->name, 0, 3)),
//                    'section_id'      => $magisterSection->id,
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
        Schema::table('magister_demo_school_location', function (Blueprint $table) {
            //
        });
    }
}
