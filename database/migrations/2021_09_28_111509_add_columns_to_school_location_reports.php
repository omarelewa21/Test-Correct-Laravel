<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSchoolLocationReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('school_location_reports', function (Blueprint $table) {
            $table->text('lvs_type')->nullable();
            $table->text('lvs_active')->nullable();
            $table->text('lvs_active_no_mail_allowed')->nullable();
            $table->text('sso_type')->nullable();
            $table->text('sso_active')->nullable();
            $table->text('allow_inbrowser_testing')->nullable();
            $table->text('intense')->nullable();
            $table->text('klantcode_schoollocatie')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->dropColumn('lvs_type');
            $table->dropColumn('lvs_active');
            $table->dropColumn('lvs_active_no_mail_allowed');
            $table->dropColumn('sso_type');
            $table->dropColumn('sso_active');
            $table->dropColumn('allow_inbrowser_testing');
            $table->dropColumn('intense');
            $table->dropColumn('klantcode_schoollocatie');
        });
    }
}
