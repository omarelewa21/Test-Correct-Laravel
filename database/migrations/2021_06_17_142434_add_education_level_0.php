<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        if (!Schema::hasColumn('education_levels', 'attainment_education_level_id')) {
            Schema::table('education_levels', function (Blueprint $table) {
                $table->integer('attainment_education_level_id')->nullable();
            });
        }
        $el = EducationLevel::create([
            'name' => 'uwlr_education_level',
            'max_years' => 8
        ]);

        $el->delete();
        DB::statement('UPDATE education_levels SET id = 0  WHERE id = ? ', [$el->getKey()]);
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
