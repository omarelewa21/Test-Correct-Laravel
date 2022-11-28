<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDrawingQuestionSvgFieldsFromBlobToLongblob extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE drawing_questions MODIFY answer_svg LONGBLOB, MODIFY question_svg LONGBLOB');
    }

    public function down()
    {
        DB::statement('ALTER TABLE drawing_questions MODIFY answer_svg BLOB, MODIFY question_svg BLOB');
    }
}