<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubsubcodeToAttainmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attainments', function (Blueprint $table) {
            $table->string('subsubcode')->nullable()->after('subcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attainments', function (Blueprint $table) {
            $table->dropColumn('subsubcode');
        });
    }
}
