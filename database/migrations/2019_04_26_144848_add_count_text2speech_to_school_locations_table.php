<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountText2speechToSchoolLocationsTable extends Migration
{
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
    {
                Schema::table('school_locations', function (Blueprint $table) {
                        $table->integer('count_text2speech')->default(0);
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
                        $table->dropColumn('count_text2speech');
                    });
            }
}