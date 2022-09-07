<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrialPeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('trial_periods', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('user_id');
            $table->dateTime('trial_until');
            $table->efficientUuid('uuid')->index()->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trial_periods');
    }
}