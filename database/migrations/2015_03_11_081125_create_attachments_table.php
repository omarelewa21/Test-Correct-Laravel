<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttachmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attachments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('question_group_id')->unsigned()->nullable()->index('fk_attachments_question_groups1_idx');
			$table->integer('question_id')->unsigned()->nullable()->index('fk_attachments_questions1_idx');
			$table->string('type', 45);
			$table->string('title')->nullable();
			$table->text('description')->nullable();
			$table->mediumText('text')->nullable();
			$table->string('link', 45)->nullable();
			$table->string('file_name')->nullable();
			$table->integer('file_size')->unsigned()->nullable();
			$table->string('file_mime_type')->nullable();
			$table->string('file_extension', 10)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('attachments');
	}

}
