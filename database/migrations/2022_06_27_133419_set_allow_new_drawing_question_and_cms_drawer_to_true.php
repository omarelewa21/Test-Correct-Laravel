<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\SchoolLocation;

class SetAllowNewDrawingQuestionAndCmsDrawerToTrue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_new_drawing_question')->default(1)->change();
            $table->boolean('allow_cms_drawer')->default(1)->change();
        });

        SchoolLocation::withTrashed()->where('allow_new_drawing_question', 0)->orWhere('allow_cms_drawer',0)->update(
            [
                'allow_new_drawing_question' => 1,
                'allow_cms_drawer' => 1,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_new_drawing_question')->default(0)->change();
            $table->boolean('allow_cms_drawer')->default(0)->change();
        });
    }
}
