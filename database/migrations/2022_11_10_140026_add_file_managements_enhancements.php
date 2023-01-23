<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;

return new class extends Migration {
    public function up()
    {
        Schema::table('file_managements', function (Blueprint $table) {
            $table->string('test_builder_code')->after('handledby')->nullable();
            $table->dateTime('planned_at')->after('deleted_at')->nullable();
            $table->integer('subject_id')->after('subject')->nullable();
        });

        DB::statement(
            'update file_managements fm
                    set fm.planned_at = fm.created_at
                    where fm.planned_at is null
                    and type = ?', [FileManagement::TYPE_TEST_UPLOAD]
        );

        /* By request of Michelle, changing this status 'Onduidelijke opdracht' to 'Bugs' with purple colorcode */
        FileManagementStatus::whereId(11)->update([
            'name' => 'Bugs',
            'colorcode' => 'colorcode-43'
        ]);
    }

    public function down()
    {
        Schema::table('file_managements', function (Blueprint $table) {
            $table->dropColumn(['test_builder_code', 'planned_at', 'subject_id']);
        });

        FileManagementStatus::whereId(11)->update([
            'name' => 'Onduidelijke opdracht',
            'colorcode' => 'colorcode-47'
        ]);
    }
};