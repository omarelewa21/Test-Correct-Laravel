<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOnboardingreportInvitedUsersColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->text('invited_users')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->string('invited_users')->nullable()->change();
        });
    }
}
