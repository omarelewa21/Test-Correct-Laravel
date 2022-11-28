<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowInOnboardingToBaseSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('base_subjects', function (Blueprint $table) {
            $table->boolean('show_in_onboarding')->default(0);
        });
        \tcCore\BaseSubject::where('name','not like','CITO%')->update(['show_in_onboarding'=>1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('base_subjects', function (Blueprint $table) {
//            $table->dropColumn('html_specialchars_encoded');
            //Up and down column not corresponding - Roan 24-08-2021;
            $table->dropColumn('show_in_onboarding');
        });
    }
}
