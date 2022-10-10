<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrialPeriodToOnboardingWizardReports extends Migration
{
    public function up()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->dateTime('trial_period_end')->nullable();
        });
    }

    public function down()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->dropColumn('trial_period_end');
        });
    }
}