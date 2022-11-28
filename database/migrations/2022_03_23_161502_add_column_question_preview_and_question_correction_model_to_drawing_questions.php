<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnQuestionPreviewAndQuestionCorrectionModelToDrawingQuestions extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE drawing_questions ADD question_preview LONGBLOB, ADD question_correction_model LONGBLOB');
    }

    public function down()
    {
        Schema::table('drawing_questions', function (Blueprint $table) {
            $table->dropColumn(['question_preview', 'question_correction_model']);
        });
    }
}