<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupquestionTypeAndNumberToGroupQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_questions', function (Blueprint $table) {
            $table->string('groupquestion_type', 50)->nullable();
            $table->integer('number_of_subquestions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_questions', function (Blueprint $table) {
            $table->dropColumn(['groupquestion_type']);
            $table->dropColumn(['number_of_subquestions']);
        });
    }
}
