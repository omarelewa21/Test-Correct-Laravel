<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInviteeToDemoTeacherRegistrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('demo_teacher_registrations', function (Blueprint $table) {
            $table->integer('invitee')->nullable();
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
            $table->dropColumn('invitee');
        });
    }
}
