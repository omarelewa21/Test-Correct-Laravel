<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDrawingQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('drawing_questions', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->index('fk_drawing_questions_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->binary('answer')->nullable();
			$table->string('bg_name')->nullable();
			$table->integer('bg_size')->unsigned()->nullable();
			$table->string('bg_mime_type')->nullable();
			$table->string('bg_extension', 10)->nullable();
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
		Schema::drop('drawing_questions');
	}

}
