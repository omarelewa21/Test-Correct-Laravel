<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDrawingQuestionsAddGridField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('drawing_questions', function(Blueprint $table)
		{
			$table->integer('grid')->unsigned()->nullable()->after('answer');
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
			$table->dropColumn('grid');
		});
	}

}
