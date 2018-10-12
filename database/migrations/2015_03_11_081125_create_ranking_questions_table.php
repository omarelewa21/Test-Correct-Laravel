<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRankingQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ranking_questions', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->index('fk_ranking_questions_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->boolean('random_order')->nullable();
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
		Schema::drop('ranking_questions');
	}

}
