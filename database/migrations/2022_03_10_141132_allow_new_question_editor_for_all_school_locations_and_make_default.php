<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\SchoolLocation;

class AllowNewQuestionEditorForAllSchoolLocationsAndMakeDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_new_question_editor')->default(1)->change();
        });

        SchoolLocation::where('allow_new_question_editor', 0)->update(['allow_new_question_editor' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_new_question_editor')->default(0)->change();
        });
    }
}
