<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailAndEmailConfirmedToDemoTeacherRegistrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('demo_teacher_registrations', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->integer('registration_email_confirmed')->nullable();
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
            $table->dropColumn('email');
            $table->dropColumn('registration_email_confirmed');
        });
    }
}
