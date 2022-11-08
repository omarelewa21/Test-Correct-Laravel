<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveAudioAttachmentSettingsFromAttachmentsToQuestionAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_attachments', function (Blueprint $table) {
           $table->text('options')->nullable();
        });
        DB::statement(
        "UPDATE question_attachments
                SET question_attachments.options = (
                    SELECT json
                    FROM attachments
                    WHERE attachments.id = question_attachments.attachment_id
                );"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('question_attachments', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
}
