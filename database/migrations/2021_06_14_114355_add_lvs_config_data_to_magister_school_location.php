<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLvsConfigDataToMagisterSchoolLocation extends Migration
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
//
//            $location->lvs = true;
//            $location->lvs_active = true;
//            $location->lvs_type = \tcCore\SchoolLocation::LVS_MAGISTER;
//            $location->save();
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
