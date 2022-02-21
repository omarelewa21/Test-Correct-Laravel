<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\SchoolLocation;

class MakeNewStudentEnvironmentDefaultAndSetForAllSchoolLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_new_student_environment')->default(1)->change();
        });

        SchoolLocation::where('allow_new_student_environment', 0)->update(['allow_new_student_environment' => 1]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_new_student_environment')->default(0)->change();
        });
    }
}
