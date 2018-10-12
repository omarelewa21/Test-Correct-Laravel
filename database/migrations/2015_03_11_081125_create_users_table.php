<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('sales_organization_id')->unsigned()->nullable()->index('fk_users_sales_organizations1_idx');
			$table->integer('school_id')->unsigned()->nullable()->index('fk_users_schools1_idx');
			$table->integer('school_location_id')->unsigned()->nullable()->index('fk_users_school_locations1_idx');
			$table->string('username', 60)->nullable();
			$table->string('password', 60)->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->string('session_hash', 100)->nullable();
			$table->string('name_first', 45)->nullable();
			$table->string('name_suffix', 45)->nullable();
			$table->string('name', 45)->nullable();
			$table->string('abbreviation', 10)->nullable();
			$table->string('external_id', 45)->nullable();
			$table->string('api_key', 100)->nullable()->unique();
			$table->enum('gender', array('Male','Female','Other'))->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
