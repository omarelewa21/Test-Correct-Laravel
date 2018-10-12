<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOpenQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_questions', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->index('fk_open_questions_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->string('subtype', 45);
			$table->text('answer', 65535)->nullable();
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
		Schema::drop('open_questions');
	}

}
