<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHtmlSpecialcharsEncodedToQuestionsTable extends Migration
{
    /**
     * update questions set html_specialchars_encoded = 0 where created_at < '2021-??-?? 00:00:00'
     */

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('html_specialchars_encoded')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('html_specialchars_encoded');
        });
    }
}
