<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tests', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('subject_id')->unsigned()->index('fk_tests_subjects1_idx');
			$table->integer('education_level_id')->unsigned()->index('fk_tests_education_levels1_idx');
			$table->integer('period_id')->unsigned()->index('fk_tests_periods1_idx');
			$table->integer('author_id')->unsigned()->index('fk_tests_users1_idx');
			$table->integer('test_kind_id')->unsigned()->index('fk_tests_test_kind1_idx');
			$table->string('name', 45)->nullable();
			$table->string('abbreviation', 20)->nullable();
			$table->integer('education_level_year')->unsigned();
			$table->integer('status')->default(0);
			$table->text('introduction', 65535)->nullable();
			$table->boolean('shuffle');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tests');
	}

}
