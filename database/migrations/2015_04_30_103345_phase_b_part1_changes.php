<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart1Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
        ALTER TABLE `answer_ratings`
        DROP FOREIGN KEY `fk_answer_ratings_users1`,
        DROP FOREIGN KEY `fk_answer_ratings_test_ratings1`;
        */
        Schema::table('answer_ratings', function(Blueprint $table)
        {
            $table->dropForeign('fk_answer_ratings_users1');
            $table->dropForeign('fk_answer_ratings_test_ratings1');
        });

        /**
        ALTER TABLE `test_takes`
        ADD COLUMN `discussing_question_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `period_id`,
        ADD INDEX `fk_test_takes_questions1_idx` (`discussing_question_id` ASC);
         */
        Schema::table('test_takes', function(Blueprint $table)
        {
            $table->integer('discussing_question_id')->unsigned()->nullable()->index('fk_test_takes_questions1_idx')->after('period_id');
            $table->enum('discussion_type', ['ALL', 'OPEN_ONLY'])->nullable()->after('invigilator_note');
            $table->dateTime('show_results')->nullable()->after('discussion_type');
        });

        /**
        ALTER TABLE `answers`
        ADD COLUMN `final_rating` DECIMAL(11,1) NULL DEFAULT NULL AFTER `done`;
         */
        Schema::table('answers', function(Blueprint $table)
        {
            $table->decimal('final_rating', 11, 1)->unsigned()->nullable()->after('done');
        });

        /**
        ALTER TABLE `answer_ratings`
        DROP COLUMN `test_rating_id`,
        CHANGE COLUMN `user_id` `user_id` INT(10) UNSIGNED NULL DEFAULT NULL ,
        ADD COLUMN `test_take_id` INT(10) UNSIGNED NOT NULL AFTER `user_id`,
        ADD COLUMN `type` ENUM('SYSTEM', 'STUDENT', 'TEACHER') NOT NULL AFTER `test_take_id`,
        ADD COLUMN `advise` VARCHAR(45) NULL DEFAULT NULL AFTER `rating`,
        ADD INDEX `fk_answer_ratings_test_takes1_idx` (`test_take_id` ASC),
        DROP INDEX `fk_answer_ratings_test_ratings1_idx` ;
         */
        Schema::table('answer_ratings', function(Blueprint $table)
        {
            $table->dropIndex('fk_answer_ratings_test_ratings1_idx');
            $table->dropColumn('test_rating_id');
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->integer('test_take_id')->unsigned()->index('fk_answer_ratings_test_takes1_idx')->after('user_id');
            $table->enum('type', ['SYSTEM', 'STUDENT', 'TEACHER'])->after('test_take_id');
            $table->text('advise', 65535)->nullable();
        });

        /**
        CREATE TABLE IF NOT EXISTS `discussing_parent_questions` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `test_takes_id` INT(10) UNSIGNED NOT NULL,
        `group_question_id` INT(10) UNSIGNED NOT NULL,
        `level` INT(10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        INDEX `fk_discussing_parent_questions_test_takes1_idx` (`test_takes_id` ASC),
        INDEX `fk_discussing_parent_questions_group_questions1_idx` (`group_question_id` ASC),
        CONSTRAINT `fk_discussing_parent_questions_test_takes1`
        FOREIGN KEY (`test_takes_id`)
        REFERENCES `test_takes` (`id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('discussing_parent_questions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('test_take_id')->unsigned()->index('fk_discussing_parent_questions_test_takes1_idx');
            $table->integer('group_question_id')->unsigned()->index('fk_discussing_parent_questions_group_questions1_idx');
            $table->integer('level')->unsigned();
            $table->foreign('test_take_id', 'fk_discussing_parent_questions_test_takes1')->references('id')->on('test_takes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('group_question_id', 'fk_discussing_parent_questions_group_questions1')->references('id')->on('group_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        DROP TABLE IF EXISTS `test_rating_participants` ;
         */
        Schema::drop('test_rating_participants');

        /**
        DROP TABLE IF EXISTS `test_ratings` ;
         */
        Schema::drop('test_ratings');

        /**
        ALTER TABLE `test_takes`
        ADD CONSTRAINT `fk_test_takes_questions1`
        FOREIGN KEY (`discussing_question_id`)
        REFERENCES `questions` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
         */
        Schema::table('test_takes', function(Blueprint $table)
        {
            $table->foreign('discussing_question_id', 'fk_test_takes_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        ALTER TABLE `answer_ratings`
        ADD CONSTRAINT `fk_answer_ratings_users1`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_answer_ratings_test_takes1`
        FOREIGN KEY (`test_take_id`)
        REFERENCES `test_takes` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
         */
        Schema::table('answer_ratings', function(Blueprint $table)
        {
            $table->foreign('user_id', 'fk_answer_ratings_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('test_take_id', 'fk_answer_ratings_test_takes1')->references('id')->on('test_takes')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
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
