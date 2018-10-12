<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tests', function(Blueprint $table)
		{
			$table->foreign('education_level_id', 'fk_tests_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('period_id', 'fk_tests_periods1')->references('id')->on('periods')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('subject_id', 'fk_tests_subjects1')->references('id')->on('subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_kind_id', 'fk_tests_test_kind1')->references('id')->on('test_kinds')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('author_id', 'fk_tests_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tests', function(Blueprint $table)
		{
			$table->dropForeign('fk_tests_education_levels1');
			$table->dropForeign('fk_tests_periods1');
			$table->dropForeign('fk_tests_subjects1');
			$table->dropForeign('fk_tests_test_kind1');
			$table->dropForeign('fk_tests_users1');
		});
	}

}
