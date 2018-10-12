<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompletionQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('completion_questions', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->index('fk_completion_questions_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->string('subtype', 45);
			$table->string('rating_method', 45)->nullable();
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
		Schema::drop('completion_questions');
	}

}
