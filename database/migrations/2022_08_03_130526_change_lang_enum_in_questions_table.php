<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLangEnumInQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions MODIFY COLUMN lang ENUM('en_GB', 'nl_NL','fr_FR','de_DE','es_ES','it_IT') null");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE questions MODIFY COLUMN lang ENUM('en_GB', 'nl_NL','fr_FR','de_DE','es_ES') null");
    }
}
