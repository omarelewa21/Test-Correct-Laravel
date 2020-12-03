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
        DB::beginTransaction();
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('account_verified')->nullable();
            });

            \DB::table('users')->update(['account_verified' => Carbon::now()]);

            Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
                $table->dateTime('account_verified')->nullable();
            });
            DB::commit();
        } catch(Throwable $e) {
            DB::rollback();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('account_verified');
            });

            Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
                $table->dropColumn('account_verified');
            });

            DB::commit();
        } catch(Throwable $e) {
            DB::rollback();
        }
    }
}
