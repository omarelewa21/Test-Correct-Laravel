<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('question_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('test_id')->unsigned()->index('fk_question_groups_tests1_idx');
			$table->integer('database_question_id')->unsigned()->nullable()->index('fk_question_groups_database_questions1_idx');
			$table->string('name')->nullable();
			$table->text('text', 65535)->nullable();
			$table->integer('order')->unsigned();
			$table->boolean('shuffle');
			$table->boolean('maintain_position');
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
		Schema::drop('question_groups');
	}

}
