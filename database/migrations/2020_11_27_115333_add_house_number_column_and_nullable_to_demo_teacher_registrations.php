<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHouseNumberColumnAndNullableToDemoTeacherRegistrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('demo_teacher_registrations', function (Blueprint $table) {
            $table->string('house_number');

            $table->text('subjects')->nullable()->change();
            $table->string('mobile')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('demo_teacher_registrations', function (Blueprint $table) {
           $table->dropColumn(['house_number']);
            $table->text('subjects')->nullable(false)->change();
            $table->string('mobile')->nullable(false)->change();
        });
    }
}
