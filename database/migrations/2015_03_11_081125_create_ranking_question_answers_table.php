<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRankingQuestionAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ranking_question_answers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('ranking_question_id')->unsigned()->index('fk_ranking_question_answers_ranking_questions1_idx');
			$table->string('answer')->nullable();
			$table->integer('order')->unsigned()->nullable();
			$table->integer('correct_order')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ranking_question_answers');
	}

}
