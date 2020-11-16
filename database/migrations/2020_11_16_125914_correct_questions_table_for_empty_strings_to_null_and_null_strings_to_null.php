<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CorrectQuestionsTableForEmptyStringsToNullAndNullStringsToNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('questions')->where('bloom', '')->update(['bloom' => null]);
        DB::table('questions')->where('miller', '')->update(['miller' => null]);

        DB::table('questions')->where('bloom', 'null')->update(['bloom' => null]);
        DB::table('questions')->where('miller', 'null')->update(['miller' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            //
        });
    }
}
