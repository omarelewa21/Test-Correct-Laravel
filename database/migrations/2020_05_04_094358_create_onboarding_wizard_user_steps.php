<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnboardingWizardUserSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onboarding_wizard_user_steps', function (Blueprint $table) {
            $table->char('id',36)->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->char('onboarding_wizard_step_id',36);
            $table->biginteger('user_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onboarding_wizard_user_steps');
    }
}
