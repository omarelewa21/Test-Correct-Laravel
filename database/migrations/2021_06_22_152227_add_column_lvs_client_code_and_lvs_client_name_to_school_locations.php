<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLvsClientCodeAndLvsClientNameToSchoolLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->string('lvs_client_name')->nullable();
            $table->string('lvs_client_code')->nullable();
        });

        $somTodayTestSchoolLocation = \tcCore\SchoolLocation::where('external_main_code', '06SS')->where('external_sub_code', '00')->first();
        if ($somTodayTestSchoolLocation) { // if not exists skip
            $somTodayTestSchoolLocation->lvs_client_name = 'Overig';
            $somTodayTestSchoolLocation->lvs_client_code = 'Ov';
            $somTodayTestSchoolLocation->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn([
                'lvs_client_name',
                'lvs_client_code'
            ]);
            //
        });
    }
}
