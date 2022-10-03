<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotifyStudentsToTestTakes extends Migration
{
    public function up()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->boolean('notify_students')->default(1);
        });
    }

    public function down()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('notify_students');
        });
    }
}