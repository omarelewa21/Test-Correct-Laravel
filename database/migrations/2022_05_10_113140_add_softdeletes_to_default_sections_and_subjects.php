<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftdeletesToDefaultSectionsAndSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('default_sections', function (Blueprint $table) {
           $table->softDeletes();
        });

        Schema::table('default_subjects', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('default_sections', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('default_subjects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
