<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart3Changes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('test_takes', function(Blueprint $table)
		{
			$table->decimal('normalization', 6, 4)->unsigned()->nullable();
		});

		Schema::table('answers', function(Blueprint $table)
		{
			$table->boolean('ignore_for_rating')->after('final_rating');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
