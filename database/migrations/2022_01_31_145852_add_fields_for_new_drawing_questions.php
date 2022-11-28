<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsForNewDrawingQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drawing_questions', function (Blueprint $table) {
            $table->binary('answer_svg')->nullable()->after('answer');
            $table->binary('question_svg')->nullable()->after('bg_extension');
            $table->char('grid_svg', 4)->nullable()->after('grid')->default('0.00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drawing_questions', function (Blueprint $table) {
            $table->dropColumn('answer_svg');
            $table->dropColumn('question_svg');
            $table->dropColumn('grid_svg');
        });
    }
}