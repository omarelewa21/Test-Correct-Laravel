<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        //Create column with default of true to take care of all existing items;
        Schema::table('tests', function (Blueprint $table) {
            $table->boolean('draft')->default(false);
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('draft')->default(false);
        });

        //Changing the default to false afterwards;
        Schema::table('tests', function (Blueprint $table) {
            $table->boolean('draft')->default(true)->change();
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('draft')->default(true)->change();
        });
    }

    public function down()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('draft');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('draft');
        });
    }
};