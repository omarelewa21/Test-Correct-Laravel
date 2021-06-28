<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnNamesOfOnboardingWizardReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->renameColumn('nr_approved_test_files_7','nr_uploaded_test_files_7');
            $table->renameColumn('nr_approved_test_files_30','nr_uploaded_test_files_30');
            $table->renameColumn('nr_approved_test_files_60','nr_uploaded_test_files_60');
            $table->renameColumn('nr_approved_test_files_90','nr_uploaded_test_files_90');
            $table->renameColumn('nr_approved_test_files_365','nr_uploaded_test_files_365');
            $table->renameColumn('nr_approved_test_files_total','nr_uploaded_test_files_total');
            $table->renameColumn('nr_approved_classes_7','nr_uploaded_classes_7');
            $table->renameColumn('nr_approved_classes_30','nr_uploaded_classes_30');
            $table->renameColumn('nr_approved_classes_60','nr_uploaded_classes_60');
            $table->renameColumn('nr_approved_classes_90','nr_uploaded_classes_90');
            $table->renameColumn('nr_approved_classes_365','nr_uploaded_classes_365');
            $table->renameColumn('nr_approved_classes_total','nr_uploaded_classes_total');
            $table->dropColumn(['nr_tests_checked_7','nr_tests_checked_30','nr_tests_checked_60','nr_tests_checked_90','nr_tests_checked_365','nr_tests_checked_total']);
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
            //
        });
    }
}
