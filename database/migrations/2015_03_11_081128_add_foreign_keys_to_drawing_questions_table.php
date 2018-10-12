<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDrawingQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('drawing_questions', function(Blueprint $table)
		{
			$table->foreign('id', 'fk_drawing_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('drawing_questions', function(Blueprint $table)
		{
			$table->dropForeign('fk_drawing_questions_questions1');
		});
	}

}
