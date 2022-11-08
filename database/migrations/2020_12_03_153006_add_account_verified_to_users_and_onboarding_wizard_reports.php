<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddAccountVerifiedToUsersAndOnboardingWizardReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('account_verified')->nullable();
            });

            \DB::statement('UPDATE users SET account_verified = created_at');

            Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
                $table->dateTime('account_verified')->nullable();
            });
        } catch(Throwable $e) {
            Throw $e;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('account_verified');
            });

            Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
                $table->dropColumn('account_verified');
            });

        } catch(Throwable $e) {
            Throw $e;
        }
    }
}
