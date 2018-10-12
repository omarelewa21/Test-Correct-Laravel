<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('answers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('test_participant_id')->unsigned()->index('fk_test_participants_has_questions_test_participants1_idx');
			$table->integer('question_id')->unsigned()->index('fk_test_participants_has_questions_questions1_idx');
			$table->longText('json')->nullable();
			$table->binary('note')->nullable();
			$table->integer('order')->unsigned();
			$table->integer('time')->unsigned()->default(0);
			$table->boolean('done')->default(0);
			$table->unique(['test_participant_id','question_id'], 'unique_test_participants_has_questions');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('answers');
	}

}
