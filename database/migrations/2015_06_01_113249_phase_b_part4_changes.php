<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart4Changes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`messages` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		`user_id` INT(10) UNSIGNED NOT NULL,
		`subject` VARCHAR(255) NOT NULL,
		`message` TEXT NULL DEFAULT NULL,
		PRIMARY KEY (`id`),
		INDEX `fk_messages_users1_idx` (`user_id` ASC),
		CONSTRAINT `fk_messages_users1`
		FOREIGN KEY (`user_id`)
		REFERENCES `test_correct`.`users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci
		 */
		Schema::create('messages', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('user_id')->unsigned()->index('fk_messages_users1_idx');
			$table->string('subject', 255);
			$table->text('message', 65535)->nullable();
			$table->foreign('user_id', 'fk_messages_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`message_receivers` (
		`id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		`message_id` INT(10) UNSIGNED NOT NULL,
		`user_id` INT(10) UNSIGNED NOT NULL,
		`type` ENUM('TO','CC','BCC') NOT NULL,
		`read` TINYINT(1) NOT NULL DEFAULT 0,
		INDEX `fk_message_receivers_users1_idx` (`user_id` ASC),
		PRIMARY KEY (`id`),
		CONSTRAINT `fk_message_receivers_messages1`
		FOREIGN KEY (`message_id`)
		REFERENCES `test_correct`.`messages` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_message_receivers_users1`
		FOREIGN KEY (`user_id`)
		REFERENCES `test_correct`.`users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci
		 */
		Schema::create('message_receivers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('message_id')->unsigned()->index('fk_message_receivers_messages1_idx');
			$table->integer('user_id')->unsigned()->index('fk_message_receivers_users1_idx');
			$table->enum('type', ['TO', 'CC', 'BCC']);
			$table->boolean('read');
			$table->foreign('message_id', 'fk_message_receivers_messages1')->references('id')->on('messages')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_message_receivers_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messages');
		Schema::drop('messages_recievers');
	}

}
