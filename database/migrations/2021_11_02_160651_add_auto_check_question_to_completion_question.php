<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoCheckQuestionToCompletionQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('completion_questions', function (Blueprint $table) {
            $table->boolean('auto_check_answer')->default(false);
            $table->boolean('auto_check_answer_case_sensitive')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('completion_questions', function (Blueprint $table) {
            $table->dropColumn(['auto_check_answer','auto_check_answer_case_sensitive']);
        });
    }
}
