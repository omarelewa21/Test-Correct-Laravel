<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportIndexesStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->index('created_at');
        });
        Schema::table('test_takes', function (Blueprint $table) {
            $table->index('time_start');
        });
        Schema::table('test_take_status_logs', function (Blueprint $table) {
            $table->index('created_at');
        });
        Schema::table('file_managements', function (Blueprint $table) {
            $table->index('created_at');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->index('created_at');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('test_participants', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('user_roles', function (Blueprint $table) {
            $table->index('deleted_at');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex('login_logs_created_at_index');
        });
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropIndex('test_takes_time_start_index');
        });
        Schema::table('test_take_status_logs', function (Blueprint $table) {
            $table->dropIndex('test_take_status_logs_created_at_index');
        });
        Schema::table('file_managements', function (Blueprint $table) {
            $table->dropIndex('file_managements_created_at_index');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_created_at_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_deleted_at_index');
        });
        Schema::table('test_participants', function (Blueprint $table) {
            $table->dropIndex('test_participants_deleted_at_index');
        });
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropIndex('user_roles_deleted_at_index');
        });
    }
}
