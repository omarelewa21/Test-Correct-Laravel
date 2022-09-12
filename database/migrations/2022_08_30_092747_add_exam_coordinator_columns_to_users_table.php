<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExamCoordinatorColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_examcoordinator')->default(false);
            $table->enum('is_examcoordinator_for', ['NONE', 'SCHOOL_LOCATION', 'SCHOOL'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_examcoordinator');
            $table->dropColumn('is_examcoordinator_for');
        });
    }
}
