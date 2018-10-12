<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart6Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * ALTER TABLE `test_correct`.`school_classes`
         * DROP FOREIGN KEY `fk_classes_users2`,
         * DROP FOREIGN KEY `fk_classes_users1`;
         */
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign('fk_classes_users2');
            $table->dropForeign('fk_classes_users1');
        });

        /**
         * ALTER TABLE `test_correct`.`messages`
         * DROP FOREIGN KEY `fk_messages_users1`;
         */
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('fk_messages_users1');
        });

        /**
         * ALTER TABLE `test_correct`.`message_receivers`
         * DROP FOREIGN KEY `fk_message_receivers_users1`;
         */
        Schema::table('message_receivers', function (Blueprint $table) {
            $table->dropForeign('fk_message_receivers_users1');
        });

        /**
         * ALTER TABLE `test_correct`.`umbrella_organizations`
         * CHANGE COLUMN `name` `name` VARCHAR(60) NOT NULL ;
         */
        Schema::table('umbrella_organizations', function (Blueprint $table) {
            $table->string('name', 60)->change();
        });

        /**
         * ALTER TABLE `test_correct`.`schools`
         * CHANGE COLUMN `name` `name` VARCHAR(60) NOT NULL ;
         */
        Schema::table('schools', function (Blueprint $table) {
            $table->string('name', 60)->change();
        });

        /**
         * ALTER TABLE `test_correct`.`school_locations`
         * ADD COLUMN `customer_code` VARCHAR(60) NULL DEFAULT NULL AFTER `school_id`,
         * ADD COLUMN `address` VARCHAR(60) NULL DEFAULT NULL AFTER `name`,
         * ADD COLUMN `postal` VARCHAR(7) NULL DEFAULT NULL AFTER `address`,
         * ADD COLUMN `city` VARCHAR(60) NULL DEFAULT NULL AFTER `postal`,
         * ADD COLUMN `country` VARCHAR(60) NULL DEFAULT NULL AFTER `city`;
         */
        Schema::table('school_locations', function (Blueprint $table) {
            $table->string('customer_code', 60)->nullable()->after('school_id');
            $table->string('address', 60)->nullable()->after('name');
            $table->string('postal', 7)->nullable()->after('address');
            $table->string('city', 60)->nullable()->after('postal');
            $table->string('country', 60)->nullable()->after('city');
        });

        /**
        ALTER TABLE `test_correct`.`school_classes`
        DROP COLUMN `manager_id`,
        DROP COLUMN `mentor_id`,
        ADD COLUMN `education_level_year` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `name`,
        DROP INDEX `fk_classes_users2_idx` ,
        DROP INDEX `fk_classes_users1_idx` ;
         */
        Schema::table('school_classes', function(Blueprint $table)
        {
            $table->dropColumn('manager_id');
            $table->dropColumn('mentor_id');
            $table->integer('education_level_year')->unsigned()->nullable()->after('name');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`mentors` (
        `school_class_id` INT(10) UNSIGNED NOT NULL,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`school_class_id`, `user_id`),
        INDEX `fk_mentors_school_classes1_idx` (`school_class_id` ASC),
        INDEX `fk_mentors_users1_idx` (`user_id` ASC),
        CONSTRAINT `fk_mentors_school_classes1`
        FOREIGN KEY (`school_class_id`)
        REFERENCES `test_correct`.`school_classes` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_mentors_users1`
        FOREIGN KEY (`user_id`)
        REFERENCES `test_correct`.`users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('mentors', function(Blueprint $table)
        {
            $table->integer('school_class_id')->unsigned()->index('fk_mentors_school_classes1_idx');
            $table->integer('user_id')->unsigned()->index('fk_mentors_users1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_class_id', 'user_id'], '');
            $table->foreign('school_class_id', 'fk_mentors_school_classes1')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id', 'fk_mentors_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`managers` (
        `school_class_id` INT(10) UNSIGNED NOT NULL,
        `user_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`school_class_id`, `user_id`),
        INDEX `fk_managers_school_classes1_idx` (`school_class_id` ASC),
        INDEX `fk_managers_users1_idx` (`user_id` ASC),
        CONSTRAINT `fk_managers_school_classes1`
        FOREIGN KEY (`school_class_id`)
        REFERENCES `test_correct`.`school_classes` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_managers_users1`
        FOREIGN KEY (`user_id`)
        REFERENCES `test_correct`.`users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('managers', function(Blueprint $table)
        {
            $table->integer('school_class_id')->unsigned()->index('fk_managers_school_classes1_idx');
            $table->integer('user_id')->unsigned()->index('fk_managers_users1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_class_id', 'user_id'], '');
            $table->foreign('school_class_id', 'fk_managers_school_classes1')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id', 'fk_managers_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
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
