<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLvsAuthorizationCodeToSchoolLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->string('lvs_authorization_key')->nullable();
            if (!Schema::hasColumn('school_locations', 'external_main_code')) {
                $table->string('external_main_code')->nullable();
            }
            if (!Schema::hasColumn('school_locations', 'external_sub_code')) {
                $table->string('external_sub_code')->nullable();
            }
        });

        $somTodayTestSchoolLocation = \tcCore\SchoolLocation::where('external_main_code', '06SS')->where('external_sub_code', '00')->first();
        if ($somTodayTestSchoolLocation) { // if not exists skip
            $somTodayTestSchoolLocation->lvs_authorization_key = 'uuq21LVDJhvRHBOGwyvhFqrTaxkrhZVlEEmOuJNhRUdrlpIJvI+ISGkjS2PNQijqEaeqKTqCrl7s2GBOAtLkew==';
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
            $table->dropColumn('lvs_authorization_key');
        });
    }
}
