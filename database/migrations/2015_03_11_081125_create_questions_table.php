<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('questions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('test_id')->unsigned()->nullable()->index('fk_questions_tests1_idx');
			$table->integer('question_group_id')->unsigned()->nullable()->index('fk_questions_question_groups1_idx');
			$table->integer('database_question_id')->unsigned()->nullable()->index('fk_questions_database_questions1_idx');
			$table->string('type', 45)->nullable();
			$table->text('question', 65535)->nullable();
			$table->integer('score')->unsigned();
			$table->integer('order')->unsigned();
			$table->boolean('maintain_position');
			$table->boolean('discuss');
			$table->boolean('decimal_score');
			$table->boolean('add_to_database');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('questions');
	}

}
