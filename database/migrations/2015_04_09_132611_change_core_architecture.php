<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCoreArchitecture extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/**
		ALTER TABLE `questions`
		DROP FOREIGN KEY `fk_questions_tests1`,
		DROP FOREIGN KEY `fk_questions_question_groups1`,
		DROP FOREIGN KEY `fk_questions_database_questions1`;
		 */
        //disabled for sqlLite
        if(config('database.default') != 'sqlite' ) {

            Schema::table('questions', function (Blueprint $table) {
                $table->dropForeign('fk_questions_tests1');
                $table->dropForeign('fk_questions_question_groups1');
                $table->dropForeign('fk_questions_database_questions1');
            });

            DB::statement('ALTER TABLE `questions` CHANGE COLUMN `score` `score` INT(10) UNSIGNED NULL DEFAULT NULL ,
CHANGE COLUMN `decimal_score` `decimal_score` TINYINT(1) NULL DEFAULT NULL ;');

            /**
             * ALTER TABLE `question_groups`
             * DROP FOREIGN KEY `fk_question_groups_tests1`,
             * DROP FOREIGN KEY `fk_question_groups_database_questions1`;
             */
            Schema::table('question_groups', function (Blueprint $table) {
                $table->dropForeign('fk_question_groups_tests1');
                $table->dropForeign('fk_question_groups_database_questions1');
            });


            /**
             * ALTER TABLE `attachments`
             * DROP FOREIGN KEY `fk_attachments_question_groups1`;
             */
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropForeign('fk_attachments_question_groups1');
            });


            /**
             * ALTER TABLE `school_location_ips`
             * DROP FOREIGN KEY `fk_school_location_ips_school_locations1`;
             */
            Schema::table('school_location_ips', function (Blueprint $table) {
                $table->dropForeign('fk_school_location_ips_school_locations1');
            });

            /**
             * ALTER TABLE `test_correct`.`attachments`
             * DROP FOREIGN KEY `fk_attachments_questions1`;
             */
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropForeign('fk_attachments_questions1');
            });

            /**
             * ALTER TABLE `test_correct`.`ranking_question_answers`
             * DROP FOREIGN KEY `fk_ranking_question_answers_ranking_questions1`;
             */
            Schema::table('ranking_question_answers', function (Blueprint $table) {
                $table->dropForeign('fk_ranking_question_answers_ranking_questions1');
            });

            /**
             * ALTER TABLE `test_correct`.`matching_question_answers`
             * DROP FOREIGN KEY `fk_matching_question_answers_matching_questions1`;
             */
            Schema::table('matching_question_answers', function (Blueprint $table) {
                $table->dropForeign('fk_matching_question_answers_matching_questions1');
            });

            /**
             * ALTER TABLE `test_correct`.`multiple_choice_question_answers`
             * DROP FOREIGN KEY `fk_multiple_choice_question_answers_multiple_choice_questions1`;
             */
            Schema::table('multiple_choice_question_answers', function (Blueprint $table) {
                $table->dropForeign('fk_multiple_choice_question_answers_multiple_choice_questions1');
            });

            /**
             * ALTER TABLE `test_correct`.`completion_question_answers`
             * DROP FOREIGN KEY `fk_completion_question_answers_completion_questions1`;
             */
            Schema::table('completion_question_answers', function (Blueprint $table) {
                $table->dropForeign('fk_completion_question_answers_completion_questions1');
            });
        }
		/**
		ALTER TABLE `test_correct`.`test_participants`
		ADD COLUMN `answer_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `school_class_id`,
		ADD INDEX `fk_test_participants_answers1_idx` (`answer_id` ASC);

		ALTER TABLE `test_correct`.`test_participants`
		ADD CONSTRAINT `fk_test_participants_answers1`
		FOREIGN KEY (`answer_id`)
		REFERENCES `test_correct`.`answers` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
		 */
		Schema::table('test_participants', function(Blueprint $table)
		{
			$table->integer('answer_id')->unsigned()->nullable()->after('school_class_id')->index('fk_test_participants_answers1_idx');
			$table->foreign('answer_id', 'fk_test_participants_answers1')->references('id')->on('answers')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		ALTER TABLE `subjects`
		ADD COLUMN `base_subject_id` INT(10) UNSIGNED NOT NULL AFTER `section_id`,
		ADD COLUMN `abbreviation` VARCHAR(10) NULL DEFAULT NULL AFTER `name`,
		ADD INDEX `fk_subjects_base_subject1_idx` (`base_subject_id` ASC);
		 */
		Schema::table('subjects', function(Blueprint $table)
		{
			$table->integer('base_subject_id')->unsigned()->after('section_id')->index('fk_subjects_base_subject1_idx');
			$table->string('abbreviation', 10)->nullable()->after('name');
		});

		/**
		ALTER TABLE `tests`
		ADD COLUMN `system_test_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `test_kind_id`,
		ADD COLUMN `is_system_test` TINYINT(1) NOT NULL AFTER `shuffle`,
		ADD INDEX `fk_tests_tests1_idx` (`system_test_id` ASC);
		 */
		Schema::table('tests', function(Blueprint $table)
		{
			$table->integer('system_test_id')->unsigned()->nullable()->after('test_kind_id')->index('fk_tests_tests1_idx');
			$table->boolean('is_system_test')->after('shuffle');
		});

		/**
		ALTER TABLE `questions`
		ADD COLUMN `subject_id` INT(10) UNSIGNED NOT NULL AFTER `deleted_at`,
		ADD COLUMN `education_level_id` INT(10) UNSIGNED NOT NULL AFTER `subject_id`,
		ADD COLUMN `is_subquestion` TINYINT(1) NOT NULL DEFAULT 0 AFTER `add_to_database`,
		ADD COLUMN `derived_question_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `is_subquestion`,
		ADD INDEX `fk_questions_subjects1_idx` (`subject_id` ASC),
		ADD INDEX `fk_questions_education_levels1_idx` (`education_level_id` ASC),
		ADD INDEX `fk_questions_periods1_idx` (`period_id` ASC),
		ADD INDEX `fk_questions_questions1_idx` (`derived_question_id` ASC),
		DROP INDEX `fk_questions_database_questions1_idx` ,
		DROP INDEX `fk_questions_question_groups1_idx` ,
		DROP INDEX `fk_questions_tests1_idx` ;
		 */
		Schema::table('questions', function(Blueprint $table)
		{
			$table->integer('subject_id')->unsigned()->after('deleted_at')->index('fk_questions_subjects1_idx');
			$table->integer('education_level_id')->unsigned()->after('subject_id')->index('fk_questions_education_levels1_idx');
			$table->integer('education_level_year')->unsigned()->after('question');
			$table->enum('note_type', ['NONE', 'TEXT', 'DRAWING'])->default('NONE')->after('decimal_score');
			$table->boolean('is_subquestion')->after('add_to_database');
			$table->integer('derived_question_id')->unsigned()->nullable()->after('is_subquestion')->index('fk_questions_questions1_idx');
			$table->dropIndex('fk_questions_database_questions1_idx');
			$table->dropIndex('fk_questions_question_groups1_idx');
			$table->dropIndex('fk_questions_tests1_idx');
		});

		/**
		ALTER TABLE `question_groups`
		CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL ,
		ADD INDEX `fk_group_questions_questions1_idx` (`id` ASC),
		DROP INDEX `fk_question_groups_database_questions1_idx` ,
		DROP INDEX `fk_question_groups_tests1_idx`;
		 */
		Schema::table('question_groups', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->change();
			$table->index('id', 'fk_group_questions_questions1_idx');
		});


		Schema::create('group_questions', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->index();//'fk_group_questions_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->string('name')->nullable();
			$table->boolean('shuffle');
			$table->primary(['id'], '');
		});

		/**
		ALTER TABLE `attachments`
		DROP INDEX `fk_attachments_question_groups1_idx` ;
		 */
		Schema::table('attachments', function(Blueprint $table)
		{
			$table->dropIndex('fk_attachments_question_groups1_idx');
		});

		/**
		ALTER TABLE `test_correct`.`attachments`
		DROP INDEX `fk_attachments_questions1_idx` ;
		 */
		Schema::table('attachments', function(Blueprint $table)
		{
			$table->dropIndex('fk_attachments_questions1_idx');
		});

		/**
		ALTER TABLE `test_correct`.`ranking_question_answers`
		DROP INDEX `fk_ranking_question_answers_ranking_questions1_idx` ;
		 */
		Schema::table('ranking_question_answers', function(Blueprint $table)
		{
			$table->dropIndex('fk_ranking_question_answers_ranking_questions1_idx');
		});

		/**
		ALTER TABLE `test_correct`.`matching_question_answers`
		DROP INDEX `fk_matching_question_answers_matching_questions1_idx` ;
		 */
		Schema::table('matching_question_answers', function(Blueprint $table)
		{
			$table->dropIndex('fk_matching_question_answers_matching_questions1_idx');
		});

		/**
		ALTER TABLE `test_correct`.`multiple_choice_question_answers`
		DROP INDEX `fk_multiple_choice_question_answers_multiple_choice_questio_idx` ;
		 */
		Schema::table('multiple_choice_question_answers', function(Blueprint $table)
		{
			$table->dropIndex('fk_multiple_choice_question_answers_multiple_choice_questio_idx');
		});

		/**
		ALTER TABLE `test_correct`.`completion_question_answers`
		ADD COLUMN `correct` TINYINT(1) NOT NULL DEFAULT 0 AFTER `answer`,
		DROP INDEX `fk_completion_question_answers_completion_questions1_idx` ;
		 */
		Schema::table('completion_question_answers', function(Blueprint $table)
		{
			$table->dropIndex('fk_completion_question_answers_completion_questions1_idx');
			$table->boolean('correct')->after('answer');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_questions` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		`test_id` INT(10) UNSIGNED NOT NULL,
		`question_id` INT(10) UNSIGNED NOT NULL,
		`order` INT(10) UNSIGNED NOT NULL,
		`maintain_position` TINYINT(1) NOT NULL,
		`discuss` TINYINT(1) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX `fk_test_questions_tests1_idx` (`test_id` ASC),
		INDEX `fk_test_questions_questions1_idx` (`question_id` ASC),
		CONSTRAINT `fk_test_questions_tests1`
		FOREIGN KEY (`test_id`)
		REFERENCES `tests` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_test_questions_questions1`
		FOREIGN KEY (`question_id`)
		REFERENCES `questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('test_questions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('test_id')->unsigned()->index('fk_test_questions_tests1_idx');
			$table->integer('question_id')->unsigned()->index('fk_test_questions_questions1_idx');
			$table->integer('order')->unsigned();
			$table->boolean('maintain_position');
			$table->boolean('discuss');
			$table->foreign('test_id', 'fk_test_questions_tests1')->references('id')->on('tests')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('question_id', 'fk_test_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `group_question_questions` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		`group_question_id` INT(10) UNSIGNED NOT NULL,
		`question_id` INT(10) UNSIGNED NOT NULL,
		`order` INT(11) NULL NOT NULL,
		`maintain_position` INT(11) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX `fk_group_question_questions_group_questions1_idx` (`group_question_id` ASC),
		INDEX `fk_group_question_questions_questions1_idx` (`question_id` ASC),
		CONSTRAINT `fk_group_question_questions_group_questions1`
		FOREIGN KEY (`group_question_id`)
		REFERENCES `group_questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_group_question_questions_questions1`
		FOREIGN KEY (`question_id`)
		REFERENCES `questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('group_question_questions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('group_question_id')->unsigned()->index('fk_group_question_questions_group_questions1_idx');
			$table->integer('question_id')->unsigned()->index('fk_group_question_questions_questions1_idx');
			$table->integer('order')->unsigned();
			$table->boolean('maintain_position');
			$table->boolean('discuss');
			$table->foreign('group_question_id', 'fk_group_question_questions_group_questions1')->references('id')->on('group_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('question_id', 'fk_group_question_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `base_subjects` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		`name` VARCHAR(45) NULL DEFAULT NULL,
		PRIMARY KEY (`id`))
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('base_subjects', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->string('name', 45)->nullable();
		});

		/**
		CREATE TABLE IF NOT EXISTS `school_location_subjects` (
		`school_location_id` INT(10) UNSIGNED NOT NULL,
		`subject_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		INDEX `fk_school_location_subjects_school_locations1_idx` (`school_location_id` ASC),
		INDEX `fk_school_location_subjects_subjects1_idx` (`subject_id` ASC),
		PRIMARY KEY (`school_location_id`, `subject_id`),
		CONSTRAINT `fk_school_location_subjects_school_locations1`
		FOREIGN KEY (`school_location_id`)
		REFERENCES `school_locations` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_school_location_subjects_subjects1`
		FOREIGN KEY (`subject_id`)
		REFERENCES `subjects` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('school_location_subjects', function(Blueprint $table)
		{
			$table->integer('school_location_id')->unsigned()->index('fk_school_location_subjects_school_locations1_idx');
			$table->integer('subject_id')->unsigned()->index('fk_school_location_subjects_subjects1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['school_location_id','subject_id'], '');
			$table->foreign('school_location_id', 'fk_school_location_subjects_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('subject_id', 'fk_school_location_subjects_subjects1')->references('id')->on('subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `school_location_periods` (
		`school_location_id` INT(10) UNSIGNED NOT NULL,
		`period_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		INDEX `fk_school_location_periods_school_locations1_idx` (`school_location_id` ASC),
		INDEX `fk_school_location_periods_periods1_idx` (`period_id` ASC),
		PRIMARY KEY (`school_location_id`, `period_id`),
		CONSTRAINT `fk_school_location_periods_school_locations1`
		FOREIGN KEY (`school_location_id`)
		REFERENCES `school_locations` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_school_location_periods_periods1`
		FOREIGN KEY (`period_id`)
		REFERENCES `periods` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('school_location_periods', function(Blueprint $table)
		{
			$table->integer('school_location_id')->unsigned()->index('fk_school_location_periods_school_locations1_idx');
			$table->integer('period_id')->unsigned()->index('fk_school_location_periods_periods1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['school_location_id','period_id'], '');
			$table->foreign('school_location_id', 'fk_school_location_periods_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('period_id', 'fk_school_location_periods_periods1')->references('id')->on('periods')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`question_authors` (
		`user_id` INT(10) UNSIGNED NOT NULL,
		`question_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		INDEX `fk_question_authors_users1_idx` (`user_id` ASC),
		INDEX `fk_question_authors_questions1_idx` (`question_id` ASC),
		PRIMARY KEY (`user_id`, `question_id`),
		CONSTRAINT `fk_question_authors_users1`
		FOREIGN KEY (`user_id`)
		REFERENCES `test_correct`.`users` (`id`)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
		CONSTRAINT `fk_question_authors_questions1`
		FOREIGN KEY (`question_id`)
		REFERENCES `test_correct`.`questions` (`id`)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci
		 */
		Schema::create('question_authors', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->index('fk_question_authors_users1_idx');
			$table->integer('question_id')->unsigned()->index('fk_question_authors_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['user_id', 'question_id'], '');
			$table->foreign('user_id', 'fk_question_authors_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('question_id', 'fk_question_authors_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`answer_parent_questions` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		`answer_id` INT(10) UNSIGNED NOT NULL,
		`group_question_id` INT(10) UNSIGNED NOT NULL,
		`level` INT(10) UNSIGNED NOT NULL,
		PRIMARY KEY (`id`),
		INDEX `fk_answer_parent_questions_answers1_idx` (`answer_id` ASC),
		INDEX `fk_answer_parent_questions_questions1_idx` (`question_id` ASC),
		CONSTRAINT `fk_answer_parent_questions_answers1`
		FOREIGN KEY (`answer_id`)
		REFERENCES `test_correct`.`answers` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_answer_parent_questions_group_questions1`
		FOREIGN KEY (`group_question_id`)
		REFERENCES `test_correct`.`group_questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci
		 */
		Schema::create('answer_parent_questions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('answer_id')->unsigned()->index('fk_answer_parent_questions_answers1_idx');
			$table->integer('group_question_id')->unsigned()->index('fk_answer_parent_questions_group_questions1_idx');
			$table->integer('level')->unsigned();
			$table->foreign('answer_id', 'fk_answer_parent_questions_answers1')->references('id')->on('answers')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('group_question_id', 'fk_answer_parent_questions_group_questions1')->references('id')->on('group_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`question_attachments` (
		`question_id` INT(10) UNSIGNED NOT NULL,
		`attachment_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		INDEX `fk_question_attachments_questions1_idx` (`question_id` ASC),
		INDEX `fk_question_attachments_attachments1_idx` (`attachment_id` ASC),
		PRIMARY KEY (`question_id`, `attachment_id`),
		CONSTRAINT `fk_question_attachments_questions1`
		FOREIGN KEY (`question_id`)
		REFERENCES `test_correct`.`questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_question_attachments_attachments1`
		FOREIGN KEY (`attachment_id`)
		REFERENCES `test_correct`.`attachments` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('question_attachments', function(Blueprint $table)
		{
			$table->integer('question_id')->unsigned()->index('fk_question_attachments_questions1_idx');
			$table->integer('attachment_id')->unsigned()->index('fk_question_attachments_attachments1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['question_id', 'attachment_id'], '');
			$table->foreign('question_id', 'fk_question_attachments_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('attachment_id', 'fk_question_attachments_attachments1')->references('id')->on('attachments')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`ranking_question_answer_links` (
		`ranking_question_id` INT(10) UNSIGNED NOT NULL,
		`ranking_question_answer_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		PRIMARY KEY (`ranking_question_id`, `ranking_question_answer_id`),
		INDEX `fk_ranking_question_answer_links_ranking_question_answers1_idx` (`ranking_question_answer_id` ASC),
		INDEX `fk_ranking_question_answer_links_ranking_questions1_idx` (`ranking_question_id` ASC),
		CONSTRAINT `fk_ranking_question_answer_links_ranking_question_answers1`
		FOREIGN KEY (`ranking_question_answer_id`)
		REFERENCES `test_correct`.`ranking_question_answers` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_ranking_question_answer_links_ranking_questions1`
		FOREIGN KEY (`ranking_question_id`)
		REFERENCES `test_correct`.`ranking_questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('ranking_question_answer_links', function(Blueprint $table)
		{
			$table->integer('ranking_question_id')->unsigned()->index('fk_ranking_question_answer_links_ranking_questions1_idx');
			$table->integer('ranking_question_answer_id')->unsigned()->index('fk_ranking_question_answer_links_ranking_question_answers1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('order')->unsigned();
			$table->integer('correct_order')->unsigned();
			$table->primary(['ranking_question_id', 'ranking_question_answer_id'], 'pk_something');
			$table->foreign('ranking_question_id', 'fk_ranking_question_answer_links_ranking_questions1')->references('id')->on('ranking_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('ranking_question_answer_id', 'fk_ranking_question_answer_links_ranking_question_answers1')->references('id')->on('ranking_question_answers')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`matching_question_answer_links` (
		`matching_question_id` INT(10) UNSIGNED NOT NULL,
		`matching_question_answer_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		PRIMARY KEY (`matching_question_id`, `matching_question_answer_id`),
		INDEX `fk_matching_question_answer_links_matching_question_answers_idx` (`matching_question_answer_id` ASC),
		INDEX `fk_matching_question_answer_links_matching_questions1_idx` (`matching_question_id` ASC),
		CONSTRAINT `fk_matching_question_answer_links_matching_question_answers1`
		FOREIGN KEY (`matching_question_answer_id`)
		REFERENCES `test_correct`.`matching_question_answers` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_matching_question_answer_links_matching_questions1`
		FOREIGN KEY (`matching_question_id`)
		REFERENCES `test_correct`.`matching_questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('matching_question_answer_links', function(Blueprint $table)
		{
			$table->integer('matching_question_id')->unsigned()->index('fk_matching_question_answer_links_matching_questions1_idx');
			$table->integer('matching_question_answer_id')->unsigned()->index('fk_matching_question_answer_links_matching_question_answers_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('order')->unsigned();
			$table->primary(['matching_question_id', 'matching_question_answer_id'], 'pk_matching_question_id');
			$table->foreign('matching_question_id', 'fk_matching_question_answer_links_matching_questions1')->references('id')->on('matching_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('matching_question_answer_id', 'fk_matching_question_answer_links_matching_question_answers1')->references('id')->on('matching_question_answers')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`multiple_choice_question_answer_links` (
		`multiple_choice_question_id` INT(10) UNSIGNED NOT NULL,
		`multiple_choice_question_answer_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		PRIMARY KEY (`multiple_choice_question_id`, `multiple_choice_question_answer_id`),
		INDEX `fk_multiple_choice_question_answer_links_multiple_choice_qu_idx` (`multiple_choice_question_answer_id` ASC),
		INDEX `fk_multiple_choice_question_answer_links_multiple_choice_qu_idx1` (`multiple_choice_question_id` ASC),
		CONSTRAINT `fk_multiple_choice_question_answer_links_multiple_choice_ques1`
		FOREIGN KEY (`multiple_choice_question_answer_id`)
		REFERENCES `test_correct`.`multiple_choice_question_answers` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_multiple_choice_question_answer_links_multiple_choice_ques2`
		FOREIGN KEY (`multiple_choice_question_id`)
		REFERENCES `test_correct`.`multiple_choice_questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('multiple_choice_question_answer_links', function(Blueprint $table)
		{
			$table->integer('multiple_choice_question_id')->unsigned()->index('fk_multiple_choice_question_answer_links_multiple_choice_qu_idx1');
			$table->integer('multiple_choice_question_answer_id')->unsigned()->index('fk_multiple_choice_question_answer_links_multiple_choice_qu_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('order')->unsigned();
			$table->primary(['multiple_choice_question_id', 'multiple_choice_question_answer_id'], 'pk_multiple_choice_question_id');
			$table->foreign('multiple_choice_question_id', 'fk_multiple_choice_question_answer_links_multiple_choice_ques2')->references('id')->on('multiple_choice_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('multiple_choice_question_answer_id', 'fk_multiple_choice_question_answer_links_multiple_choice_ques1')->references('id')->on('multiple_choice_question_answers')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `test_correct`.`completion_question_answer_links` (
		`completion_question_id` INT(10) UNSIGNED NOT NULL,
		`completion_question_answer_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		PRIMARY KEY (`completion_question_id`, `completion_question_answer_id`),
		INDEX `fk_completion_question_answer_links_completion_question_ans_idx` (`completion_question_answer_id` ASC),
		INDEX `fk_completion_question_answer_links_completion_questions1_idx` (`completion_question_id` ASC),
		CONSTRAINT `fk_completion_question_answer_links_completion_question_answe1`
		FOREIGN KEY (`completion_question_answer_id`)
		REFERENCES `test_correct`.`completion_question_answers` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_completion_question_answer_links_completion_questions1`
		FOREIGN KEY (`completion_question_id`)
		REFERENCES `test_correct`.`completion_questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci;
		 */
		Schema::create('completion_question_answer_links', function(Blueprint $table)
		{
			$table->integer('completion_question_id')->unsigned()->index('fk_completion_question_answer_links_completion_questions1_idx');
			$table->integer('completion_question_answer_id')->unsigned()->index('fk_completion_question_answer_links_completion_question_ans_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['completion_question_id', 'completion_question_answer_id'], 'pk_completion_question_id');
			$table->foreign('completion_question_id', 'fk_completion_question_answer_links_completion_questions1')->references('id')->on('completion_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('completion_question_answer_id', 'fk_completion_question_answer_links_completion_question_answe1')->references('id')->on('completion_question_answers')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `attainments` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		`base_subject_id` INT(10) UNSIGNED NOT NULL,
		`education_level_id` INT(10) UNSIGNED NOT NULL,
		`code` VARCHAR(45) NULL DEFAULT NULL,
		`description` TEXT NULL DEFAULT NULL,
		`status` ENUM('ACTIVE','REPLACED','OLD') NOT NULL DEFAULT 'ACTIVE',
		PRIMARY KEY (`id`),
		INDEX `fk_attainments_education_levels1_idx` (`education_level_id` ASC),
		INDEX `fk_attainments_base_subjects1_idx` (`base_subject_id` ASC),
		CONSTRAINT `fk_attainments_education_levels1`
		FOREIGN KEY (`education_level_id`)
		REFERENCES `test_correct`.`education_levels` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_attainments_base_subjects1`
		FOREIGN KEY (`base_subject_id`)
		REFERENCES `test_correct`.`base_subjects` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci
		 */
		Schema::create('attainments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('base_subject_id')->unsigned()->index('fk_attainments_base_subjects1_idx');
			$table->integer('education_level_id')->unsigned()->index('fk_attainments_education_levels1_idx');
			$table->string('code', 45);
			$table->text('description', 65535)->nullable();
			$table->enum('status', ['ACTIVE', 'REPLACED', 'OLD'])->default('ACTIVE');
			$table->foreign('education_level_id', 'fk_attainments_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('base_subject_id', 'fk_attainments_base_subjects1')->references('id')->on('base_subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		CREATE TABLE IF NOT EXISTS `question_attainments` (
		`attainment_id` INT(10) UNSIGNED NOT NULL,
		`question_id` INT(10) UNSIGNED NOT NULL,
		`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		`deleted_at` TIMESTAMP NULL DEFAULT NULL,
		PRIMARY KEY (`attainment_id`, `question_id`),
		INDEX `fk_question_attainments_attainments1_idx` (`attainment_id` ASC),
		INDEX `fk_question_attainments_questions1_idx` (`question_id` ASC),
		CONSTRAINT `fk_question_attainments_attainments1`
		FOREIGN KEY (`attainment_id`)
		REFERENCES `test_correct`.`attainments` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		CONSTRAINT `fk_question_attainments_questions1`
		FOREIGN KEY (`question_id`)
		REFERENCES `test_correct`.`questions` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci
		 */
		Schema::create('question_attainments', function(Blueprint $table)
		{
			$table->integer('attainment_id')->unsigned()->index('fk_question_attainments_attainments1_idx');
			$table->integer('question_id')->unsigned()->index('fk_question_attainments_questions1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['attainment_id', 'question_id'], 'pk_attainment_id');
			$table->foreign('attainment_id', 'fk_question_attainments_attainments1')->references('id')->on('attainments')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('question_id', 'fk_question_attainments_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});

		/**
		 * Data-Migration script
		 * question_group -> group_question
		 */
		$questionGroups = DB::table('question_groups')->get();
		foreach($questionGroups as $questionGroup) {
			$questionId = DB::table('questions')->insertGetId(
				['created_at' => $questionGroup->created_at, 'updated_at' => $questionGroup->updated_at, 'deleted_at' => $questionGroup->deleted_at, 'test_id' => $questionGroup->test_id, 'type' => 'GroupQuestion', 'question' => $questionGroup->text, 'order' => $questionGroup->order, 'maintain_position' => $questionGroup->maintain_position, 'add_to_database' => $questionGroup->add_to_database]
			);

			DB::table('group_questions')->insertGetId(
				['id' => $questionId, 'created_at' => $questionGroup->created_at, 'updated_at' => $questionGroup->updated_at, 'deleted_at' => $questionGroup->deleted_at, 'name' => $questionGroup->name, 'shuffle' => $questionGroup->shuffle]
			);

			$questionGroupsQuestions = DB::table('questions')->where('question_group_id', $questionGroup->id)->get();
			foreach($questionGroupsQuestions as $questionGroupsQuestion) {
				DB::table('group_question_questions')->insertGetId(
					['created_at' => $questionGroupsQuestion->created_at, 'updated_at' => $questionGroupsQuestion->updated_at, 'deleted_at' => $questionGroupsQuestion->deleted_at, 'group_question_id' => $questionId, 'question_id' => $questionGroupsQuestion->id, 'order' => $questionGroupsQuestion->order, 'maintain_position' => $questionGroupsQuestion->maintain_position, 'discuss' => $questionGroupsQuestion->discuss]
				);

				DB::table('questions')
					->where('id', $questionGroupsQuestion->id)
					->update(array('is_subquestion' => 1));

				$answers = DB::table('answers')->where('question_id', $questionGroupsQuestion->id)->get();
				foreach($answers as $answer) {
					DB::table('answer_parent_questions')->insertGetId(
						['created_at' => $answer->created_at, 'updated_at' => $answer->updated_at, 'deleted_at' => $answer->deleted_at, 'answer_id' => $answer->id, 'group_question_id' => $questionId, 'level' => 1]
					);
				}
			}

			$questionGroupsAttachments = DB::table('attachments')->where('question_group_id', $questionGroup->id)->get();
			foreach($questionGroupsAttachments as $questionGroupsAttachment) {
				DB::table('attachments')
					->where('id', $questionGroupsAttachment->id)
					->update(['question_id' => $questionId]);
			}
		}

		/**
		 * Data-Migration script
		 * Reattach questions to tests and authors
		 */

		$questions = DB::table('questions')->get();
		foreach($questions as $question) {
			if ($question->test_id === null) {
				$questionGroup = DB::table('question_groups')->where('id', $question->question_group_id)->first();
				$test = DB::table('tests')->where('id', $questionGroup->test_id)->first();
			} else {
				$test = DB::table('tests')->where('id', $question->test_id)->first();
			}

			if ($question->test_id !== null) {
				$questionId = DB::table('test_questions')->insertGetId(
					['created_at' => $question->created_at, 'updated_at' => $question->updated_at, 'deleted_at' => $question->deleted_at, 'test_id' => $test->id, 'question_id' => $question->id, 'order' => $question->order, 'maintain_position' => $question->maintain_position, 'discuss' => $question->discuss]
				);
			}

			DB::table('questions')
				->where('id', $question->id)
				->update(['subject_id' => $test->subject_id, 'education_level_id' => $test->education_level_id]);

			DB::table('question_authors')->insertGetId(
				['created_at' => $question->created_at, 'updated_at' => $question->updated_at, 'deleted_at' => $question->deleted_at, 'question_id' => $question->id, 'user_id' => $test->author_id]
			);
		}

		/**
		 * Data-Migration script
		 * Add all subjects to a base subject
		 */
        $insertGetId = ['created_at' => DB::raw('now()'), 'updated_at' => DB::raw('now()'), 'deleted_at' => null, 'name' => 'Anders'];
        if (config('database.default') == 'sqlite') {
            $insertGetId = ['created_at' => DB::raw("date('now')"), 'updated_at' => DB::raw("date('now')"), 'deleted_at' => null, 'name' => 'Anders'];
        }
		$baseSubjectId = DB::table('base_subjects')->insertGetId( $insertGetId );

		DB::table('subjects')
			->where('base_subject_id', 0)
			->update(['base_subject_id' => $baseSubjectId]);

		/**
		 * Data-Migration script
		 * Re-attach attachments to questions
		 */
		$attachments = DB::table('attachments')->get();
		foreach($attachments as $attachment) {
			DB::table('question_attachments')->insertGetId(
				['created_at' => $attachment->created_at, 'updated_at' => $attachment->updated_at, 'deleted_at' => $attachment->deleted_at, 'question_id' => $attachment->question_id, 'attachment_id' => $attachment->id]
			);
		}

		/**
		 * Data-Migration script
		 * Re-attach attachments to questions
		 */
		$rankingQuestionAnswers = DB::table('ranking_question_answers')->get();
		foreach($rankingQuestionAnswers as $rankingQuestionAnswer) {
			DB::table('ranking_question_answer_links')->insertGetId(
				[
				    'created_at' => $rankingQuestionAnswer->created_at,
                    'updated_at' => $rankingQuestionAnswer->updated_at,
                    'deleted_at' => $rankingQuestionAnswer->deleted_at,
                    'order' => ($rankingQuestionAnswer->order !== null) ? $rankingQuestionAnswer->order : 0,
                    'correct_order' => ($rankingQuestionAnswer->correct_order !== null)
                        ? $rankingQuestionAnswer->correct_order
                        : (($rankingQuestionAnswer->order !== null)
                            ? $rankingQuestionAnswer->order
                            : 0),
                    'ranking_question_id' => $rankingQuestionAnswer->ranking_question_id,
                    'ranking_question_answer_id' => $rankingQuestionAnswer->id
                ]
			);
		}

		/**
		 * Data-Migration script
		 * Re-attach attachments to questions
		 */
		$matchingQuestionAnswers = DB::table('matching_question_answers')->get();
		foreach($matchingQuestionAnswers as $matchingQuestionAnswer) {
			DB::table('matching_question_answer_links')->insertGetId(
				['created_at' => $matchingQuestionAnswer->created_at, 'updated_at' => $matchingQuestionAnswer->updated_at, 'deleted_at' => $matchingQuestionAnswer->deleted_at, 'order' => ($matchingQuestionAnswer->order !== null) ? $matchingQuestionAnswer->order : 0, 'matching_question_id' => $matchingQuestionAnswer->matching_question_id, 'matching_question_answer_id' => $matchingQuestionAnswer->id]
			);
		}

		/**
		 * Data-Migration script
		 * Re-attach attachments to questions
		 */
		$multipleChoiceQuestionAnswers = DB::table('multiple_choice_question_answers')->get();
		foreach($multipleChoiceQuestionAnswers as $multipleChoiceQuestionAnswer) {
			DB::table('multiple_choice_question_answer_links')->insertGetId(
				['created_at' => $multipleChoiceQuestionAnswer->created_at, 'updated_at' => $multipleChoiceQuestionAnswer->updated_at, 'deleted_at' => $multipleChoiceQuestionAnswer->deleted_at, 'order' => ($multipleChoiceQuestionAnswer->order !== null) ? $multipleChoiceQuestionAnswer->order : 0, 'multiple_choice_question_id' => $multipleChoiceQuestionAnswer->multiple_choice_question_id, 'multiple_choice_question_answer_id' => $multipleChoiceQuestionAnswer->id]
			);
		}

		/**
		 * Data-Migration script
		 * Re-attach attachments to questions
		 */
		$completionQuestionAnswers = DB::table('completion_question_answers')->get();
		foreach($completionQuestionAnswers as $completionQuestionAnswer) {
			DB::table('completion_question_answer_links')->insertGetId(
				['created_at' => $completionQuestionAnswer->created_at, 'updated_at' => $completionQuestionAnswer->updated_at, 'deleted_at' => $completionQuestionAnswer->deleted_at, 'completion_question_id' => $completionQuestionAnswer->completion_question_id, 'completion_question_answer_id' => $completionQuestionAnswer->id]
			);
		}

		/**
		ALTER TABLE `questions`
		DROP COLUMN `discuss`,
		DROP COLUMN `maintain_position`,
		DROP COLUMN `order`,
		DROP COLUMN `database_question_id`,
		DROP COLUMN `question_group_id`,
		DROP COLUMN `test_id`;
		 */
        if (config('database.default') != 'sqlite') {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('discuss');
                $table->dropColumn('maintain_position');
                $table->dropColumn('order');
                $table->dropColumn('database_question_id');
                $table->dropColumn('question_group_id');
                $table->dropColumn('test_id');
            });

            /**
             * ALTER TABLE `question_groups`
             * DROP COLUMN `maintain_position`,
             * DROP COLUMN `order`,
             * DROP COLUMN `text`,
             * DROP COLUMN `database_question_id`,
             * DROP COLUMN `test_id`;
             */

            /**
             * ALTER TABLE `attachments`
             * DROP COLUMN `question_group_id`,
             * DROP COLUMN `question_id` ;
             */
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropColumn('question_id');
                $table->dropColumn('question_group_id');
            });

            /**
             * ALTER TABLE `question_groups`
             * DROP COLUMN `maintain_position`,
             * DROP COLUMN `order`,
             * DROP COLUMN `text`,
             * DROP COLUMN `database_question_id`,
             * DROP COLUMN `test_id`;
             *
             * ALTER TABLE `question_groups`
             * RENAME TO  `group_questions`;
             */
            Schema::drop('question_groups');

            /**
             * ALTER TABLE `test_correct`.`ranking_question_answers`
             * DROP COLUMN `ranking_question_id` ;
             */
            Schema::table('ranking_question_answers', function (Blueprint $table) {
                $table->dropColumn('ranking_question_id');
                $table->dropColumn('order');
                $table->dropColumn('correct_order');
            });

            /**
             * ALTER TABLE `test_correct`.`matching_question_answers`
             * DROP COLUMN `matching_question_id` ;
             */
            Schema::table('matching_question_answers', function (Blueprint $table) {
                $table->dropColumn('matching_question_id');
                $table->dropColumn('order');
            });

            /**
             * ALTER TABLE `test_correct`.`multiple_choice_question_answers`
             * DROP COLUMN `multiple_choice_question_id` ;
             */
            Schema::table('multiple_choice_question_answers', function (Blueprint $table) {
                $table->dropColumn('multiple_choice_question_id');
                $table->dropColumn('order');
            });

            /**
             * ALTER TABLE `test_correct`.`completion_question_answers`
             * DROP COLUMN `completion_question_id` ;
             */
            Schema::table('completion_question_answers', function (Blueprint $table) {
                $table->dropColumn('completion_question_id');
            });

            /**
             * DROP TABLE IF EXISTS `database_questions` ;
             */
            Schema::drop('database_questions');

            /**
             * ALTER TABLE `subjects`
             * ADD CONSTRAINT `fk_subjects_base_subject1`
             * FOREIGN KEY (`base_subject_id`)
             * REFERENCES `base_subjects` (`id`)
             * ON DELETE CASCADE
             * ON UPDATE CASCADE;
             */
            Schema::table('subjects', function (Blueprint $table) {
                $table->foreign('base_subject_id', 'fk_subjects_base_subject1')->references('id')->on('base_subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
            });

            /**
             * ALTER TABLE `tests`
             * ADD CONSTRAINT `fk_tests_tests1`
             * FOREIGN KEY (`system_test_id`)
             * REFERENCES `tests` (`id`)
             * ON DELETE CASCADE
             * ON UPDATE CASCADE;
             */
            Schema::table('tests', function (Blueprint $table) {
                $table->foreign('system_test_id', 'fk_tests_tests1')->references('id')->on('tests')->onUpdate('CASCADE')->onDelete('CASCADE');
            });

            /**
             * ALTER TABLE `questions`
             * ADD CONSTRAINT `fk_questions_subjects1`
             * FOREIGN KEY (`subject_id`)
             * REFERENCES `subjects` (`id`)
             * ON DELETE CASCADE
             * ON UPDATE CASCADE,
             * ADD CONSTRAINT `fk_questions_education_levels1`
             * FOREIGN KEY (`education_level_id`)
             * REFERENCES `education_levels` (`id`)
             * ON DELETE CASCADE
             * ON UPDATE CASCADE,
             * ADD CONSTRAINT `fk_questions_questions1`
             * FOREIGN KEY (`derived_question_id`)
             * REFERENCES `questions` (`id`)
             * ON DELETE NO ACTION
             * ON UPDATE NO ACTION;
             */
            Schema::table('questions', function (Blueprint $table) {
                $table->foreign('subject_id', 'fk_questions_subjects1')->references('id')->on('subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('education_level_id', 'fk_questions_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->foreign('derived_question_id', 'fk_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
            });

            /**
             * ALTER TABLE `question_groups`
             * ADD CONSTRAINT `fk_group_questions_questions1`
             * FOREIGN KEY (`id`)
             * REFERENCES `questions` (`id`)
             * ON DELETE CASCADE
             * ON UPDATE CASCADE;
             */
            Schema::table('group_questions', function (Blueprint $table) {
                $table->foreign('id', 'fk_group_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
            });

            /**
             * ALTER TABLE `school_location_ips`
             * ADD CONSTRAINT `fk_school_location_ips_school_locations1`
             * FOREIGN KEY (`school_location_id`)
             * REFERENCES `school_locations` (`id`)
             * ON DELETE CASCADE
             * ON UPDATE CASCADE;
             */
            Schema::table('school_location_ips', function (Blueprint $table) {
                $table->foreign('school_location_id', 'fk_school_location_ips_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		throw new Exception('One-way migration ONLY');
	}

}
