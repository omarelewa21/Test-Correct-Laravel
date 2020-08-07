<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraReportColumnsToOnboardingWizardReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->string('invited_by')->nullable();
            $table->string('invited_users_amount')->nullable();
            $table->string('invited_users')->nullable();
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
            $table->dropColumn([
                'invited_by',
                'invited_users_amount',
                'invited_users',
            ]);
        });
    }
}
