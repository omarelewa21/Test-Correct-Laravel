<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSchoolLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('show_exam_material')->default(true);
            $table->boolean('show_cito_quick_test_start')->default(true);
            $table->boolean('show_test_correct_content')->default(true);
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
            $table->dropColumn('show_exam_material');
            $table->dropColumn('show_cito_quick_test_start');
            $table->dropColumn('show_test_correct_content');
        });
    }
}
