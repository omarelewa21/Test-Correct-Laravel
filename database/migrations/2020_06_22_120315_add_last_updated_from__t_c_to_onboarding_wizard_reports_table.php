<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastUpdatedFromTCToOnboardingWizardReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \tcCore\OnboardingWizardReport::all()->each->forceDelete();

        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->addColumn('datetime','last_updated_from_TC');

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
            $table->dropColumn('last_updated_from_TC');
            //
        });
    }
}
