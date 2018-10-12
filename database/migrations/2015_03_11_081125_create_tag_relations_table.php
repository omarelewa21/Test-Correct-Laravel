<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTagRelationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tag_relations', function(Blueprint $table)
		{
			$table->integer('tag_id')->unsigned()->index('fk_tag_relations_tags1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('tag_relations_id')->unsigned();
			$table->string('tag_relations_type', 45)->default('');
			$table->primary(['tag_relations_id','tag_relations_type','tag_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tag_relations');
	}

}
