<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\EducationLevel;

class AddEducationLevel0 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->integer('attainment_education_level_id')->nullable();
        });
        $el = EducationLevel::create([
            'name' => 'uwlr_education_level',
            'max_years' => 8
        ]);
        $el->id = 0;
        $el->save();
        $el->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('education_levels', function (Blueprint $table) {
            optional(EducationLevel::find(0))->forceDelete();
        });
    }
}
