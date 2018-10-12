<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMultipleChoiceQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('multiple_choice_questions', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->index('fk_multiple_choice_questions_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->string('subtype', 45)->default('');
			$table->integer('selectable_answers')->unsigned()->default(1);
			$table->primary(['id'], '');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('multiple_choice_questions');
	}

}
