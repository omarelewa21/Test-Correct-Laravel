<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TrialPeriod;

class AddSchoolLocationIdToTrialPeriods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trial_periods', function (Blueprint $table) {
            $table->bigInteger('school_location_id')->nullable();
        });

        TrialPeriod::whereNull('deleted_at')->each(function(TrialPeriod $tp){
            logger('school location id '.$tp->user->school_location_id);
           $tp->update(['school_location_id' => $tp->user->school_location_id]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trial_periods', function (Blueprint $table) {
            $table->dropColumn(['school_location_id']);
        });
    }
}
