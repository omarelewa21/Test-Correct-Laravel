<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeacherIdAndToSchoolClassImportLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_class_import_logs', function (Blueprint $table) {
            $table->integer('checked_by_teacher_id')->nullable()->after('checked_by_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_class_import_logs', function (Blueprint $table) {
            $table->dropColumn(['checked_by_teacher_id']);
        });
    }
}
