<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnboardingWizardUserStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onboarding_wizard_user_states', function (Blueprint $table) {
            $table->char('id')->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->biginteger('user_id')->index();
            $table->char('onboarding_wizard_id',36);
            $table->boolean('show')->default(true);
            $table->integer('active_step')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onboarding_wizard_user_states');
    }
}
