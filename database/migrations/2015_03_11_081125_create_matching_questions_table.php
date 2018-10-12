<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMatchingQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('matching_questions', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->index('fk_matching_questions_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->string('subtype', 45)->default('');
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
		Schema::drop('matching_questions');
	}

}
