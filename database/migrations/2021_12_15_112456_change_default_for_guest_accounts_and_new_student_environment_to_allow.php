<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDefaultForGuestAccountsAndNewStudentEnvironmentToAllow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_guest_accounts')->default(1)->change();
//            $table->boolean('allow_new_student_environment')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_guest_accounts')->default(0)->change();
//            $table->boolean('allow_new_student_environment')->default(0)->change();
        });
    }
}
