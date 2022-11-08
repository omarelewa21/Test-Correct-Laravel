<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteTrialPeriodsWithNoSchoolLocationIdFromTrialPeriods extends Migration
{

    public function up()
    {
        \tcCore\TrialPeriod::whereNull('school_location_id')
            ->whereNull('deleted_at')
            ->update(['deleted_at' => \Carbon\Carbon::now()]);
    }

    public function down()
    {

    }
}