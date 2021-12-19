<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveTypedetailsToColumnsForFileManagements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('file_managements', function (Blueprint $table) {
                $table->string('class')->nullable();
                $table->string('subject')->nullable();
                $table->integer('education_level_year')->default(0);
                $table->integer('education_level_id')->default(0);
                $table->integer('test_kind_id')->default(0);
                $table->string('test_name')->nullable();
                $table->text('orig_filenames')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_managements', function (Blueprint $table) {
            $table->dropColumn(['class','subject','education_level_year','education_level_id','test_kind_id','test_name','orig_filenames']);
        });
    }
}
