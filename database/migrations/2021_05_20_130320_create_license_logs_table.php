<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\License;

class CreateLicenseLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('license_id');
            $table->integer('amount');
            $table->integer('amount_change');
            $table->timestamps();
        });

        License::all()->each(function(License $l){
           \tcCore\LicenseLog::create([
              'license_id' => $l->getKey(),
              'amount' => $l->amount,
               'amount_change' => $l->amount,
              'created_at' => $l->created_at,
              'updated_at' => $l->created_at
           ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('license_logs');
    }
}
