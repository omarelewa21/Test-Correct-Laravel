<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuestAccountsToTestTakesAndGuestClassToSchoolClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->boolean('guest_accounts')->default(false);
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->boolean('guest_class')->default(false);
            $table->integer('test_take_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('guest_accounts');
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn(['guest_class', 'test_take_id']);
        });
    }
}
