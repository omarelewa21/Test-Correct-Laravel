<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart7Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
        ALTER TABLE `test_correct`.`school_locations`
        DROP COLUMN `country`,
        DROP COLUMN `city`,
        DROP COLUMN `postal`,
        DROP COLUMN `address`;
         */
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->dropColumn('city');
            $table->dropColumn('postal');
            $table->dropColumn('address');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`contacts` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `name` VARCHAR(45) NULL DEFAULT NULL,
        `address` VARCHAR(60) NULL DEFAULT NULL,
        `portal` VARCHAR(7) NULL DEFAULT NULL,
        `city` VARCHAR(60) NULL DEFAULT NULL,
        `country` VARCHAR(60) NULL DEFAULT NULL,
        `phone` VARCHAR(15) NULL DEFAULT NULL,
        `mobile` VARCHAR(15) NULL DEFAULT NULL,
        PRIMARY KEY (`id`))
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('contacts', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('name', 45);
            $table->string('address', 60);
            $table->string('postal', 7);
            $table->string('city', 60);
            $table->string('country', 60);
            $table->string('phone', 15);
            $table->string('mobile', 15);
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`umbrella_organization_contacts` (
        `umbrella_organization_id` INT(11) NOT NULL,
        `contact_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `type` VARCHAR(45) NULL DEFAULT NULL,
        INDEX `fk_umbrella_organization_contacts_umbrella_organizations1_idx` (`umbrella_organization_id` ASC),
        INDEX `fk_umbrella_organization_contacts_contacts1_idx` (`contact_id` ASC),
        PRIMARY KEY (`umbrella_organization_id`, `contact_id`),
        CONSTRAINT `fk_umbrella_organization_contacts_umbrella_organizations1`
        FOREIGN KEY (`umbrella_organization_id`)
        REFERENCES `test_correct`.`umbrella_organizations` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_umbrella_organization_contacts_contacts1`
        FOREIGN KEY (`contact_id`)
        REFERENCES `test_correct`.`contacts` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('umbrella_organization_contacts', function(Blueprint $table)
        {
            $table->integer('umbrella_organization_id')->unsigned()->index('fk_umbrella_organization_contacts_umbrella_organizations1_idx');
            $table->integer('contact_id')->unsigned()->index('fk_umbrella_organization_contacts_contacts1_idx');
            $table->enum('type', ['FINANCE', 'TECHNICAL', 'IMPLEMENTATION', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['umbrella_organization_id', 'contact_id', 'type'], 'pk_umbrella_organization_id');
            $table->foreign('umbrella_organization_id', 'fk_umbrella_organization_contacts_umbrella_organizations1')->references('id')->on('umbrella_organizations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('contact_id', 'fk_umbrella_organization_contacts_contacts1')->references('id')->on('contacts')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`school_contacts` (
        `school_id` INT(10) UNSIGNED NOT NULL,
        `contact_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `type` VARCHAR(45) NULL DEFAULT NULL,
        INDEX `fk_school_contacts_schools1_idx` (`school_id` ASC),
        INDEX `fk_school_contacts_contacts1_idx` (`contact_id` ASC),
        PRIMARY KEY (`school_id`, `contact_id`),
        CONSTRAINT `fk_school_contacts_schools1`
        FOREIGN KEY (`school_id`)
        REFERENCES `test_correct`.`schools` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_school_contacts_contacts1`
        FOREIGN KEY (`contact_id`)
        REFERENCES `test_correct`.`contacts` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('school_contacts', function(Blueprint $table)
        {
            $table->integer('school_id')->unsigned()->index('fk_school_contacts_schools1_idx');
            $table->integer('contact_id')->unsigned()->index('fk_school_contacts_contacts1_idx');
            $table->enum('type', ['FINANCE', 'TECHNICAL', 'IMPLEMENTATION', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_id', 'contact_id', 'type'], '');
            $table->foreign('school_id', 'fk_school_contacts_schools1')->references('id')->on('schools')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('contact_id', 'fk_school_contacts_contacts1')->references('id')->on('contacts')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`school_location_contacts` (
        `school_location_id` INT(10) UNSIGNED NOT NULL,
        `contacts_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `type` VARCHAR(45) NULL DEFAULT NULL,
        PRIMARY KEY (`school_locations_id`, `contacts_id`),
        INDEX `fk_school_location_contacts_school_locations1_idx` (`school_locations_id` ASC),
        INDEX `fk_school_location_contacts_contacts1_idx` (`contacts_id` ASC),
        CONSTRAINT `fk_school_location_contacts_school_locations1`
        FOREIGN KEY (`school_locations_id`)
        REFERENCES `test_correct`.`school_locations` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_school_location_contacts_contacts1`
        FOREIGN KEY (`contacts_id`)
        REFERENCES `test_correct`.`contacts` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('school_location_contacts', function(Blueprint $table)
        {
            $table->integer('school_location_id')->unsigned()->index('fk_school_location_contacts_school_locations1_idx');
            $table->integer('contact_id')->unsigned()->index('fk_school_location_contacts_contacts1_idx');
            $table->enum('type', ['FINANCE', 'TECHNICAL', 'IMPLEMENTATION', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_location_id', 'contact_id', 'type'], 'pk_school_location_id');
            $table->foreign('school_location_id', 'fk_school_location_contacts_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('contact_id', 'fk_school_location_contacts_contacts1')->references('id')->on('contacts')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`addresses` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `address` VARCHAR(60) NULL DEFAULT NULL,
        `portal` VARCHAR(7) NULL DEFAULT NULL,
        `city` VARCHAR(60) NULL DEFAULT NULL,
        `country` VARCHAR(60) NULL DEFAULT NULL,
        PRIMARY KEY (`id`))
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('addresses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('name', 45);
            $table->string('address', 60);
            $table->string('postal', 7);
            $table->string('city', 60);
            $table->string('country', 60);
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`umbrella_organization_addresses` (
        `address_id` INT(10) UNSIGNED NOT NULL,
        `umbrella_organization_id` INT(11) NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `type` VARCHAR(45) NULL DEFAULT NULL,
        INDEX `fk_umbrella_organization_addresses_addresses1_idx` (`address_id` ASC),
        INDEX `fk_umbrella_organization_addresses_umbrella_organizations1_idx` (`umbrella_organization_id` ASC),
        PRIMARY KEY (`address_id`, `umbrella_organization_id`),
        CONSTRAINT `fk_umbrella_organization_addresses_addresses1`
        FOREIGN KEY (`address_id`)
        REFERENCES `test_correct`.`addresses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_umbrella_organization_addresses_umbrella_organizations1`
        FOREIGN KEY (`umbrella_organization_id`)
        REFERENCES `test_correct`.`umbrella_organizations` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('umbrella_organization_addresses', function(Blueprint $table)
        {
            $table->integer('address_id')->unsigned()->index('fk_umbrella_organization_addresses_addresses1_idx');
            $table->integer('umbrella_organization_id')->unsigned()->index('fk_umbrella_organization_addresses_umbrella_organizations1_idx');
            $table->enum('type', ['MAIN', 'INVOICE', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['address_id', 'umbrella_organization_id', 'type'], 'pk_address_id');
            $table->foreign('address_id', 'fk_umbrella_organization_addresses_addresses1')->references('id')->on('addresses')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('umbrella_organization_id', 'fk_umbrella_organization_addresses_umbrella_organizations1')->references('id')->on('umbrella_organizations')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`school_addresses` (
        `address_id` INT(10) UNSIGNED NOT NULL,
        `school_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `type` VARCHAR(45) NULL DEFAULT NULL,
        INDEX `fk_school_addresses_addresses1_idx` (`address_id` ASC),
        INDEX `fk_school_addresses_schools1_idx` (`school_id` ASC),
        PRIMARY KEY (`address_id`, `school_id`),
        CONSTRAINT `fk_school_addresses_addresses1`
        FOREIGN KEY (`address_id`)
        REFERENCES `test_correct`.`addresses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_school_addresses_schools1`
        FOREIGN KEY (`school_id`)
        REFERENCES `test_correct`.`schools` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('school_addresses', function(Blueprint $table)
        {
            $table->integer('address_id')->unsigned()->index('fk_school_addresses_addresses1_idx');
            $table->integer('school_id')->unsigned()->index('fk_school_addresses_schools1_idx');
            $table->enum('type', ['MAIN', 'INVOICE', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['address_id', 'school_id', 'type'], '');
            $table->foreign('address_id', 'fk_school_addresses_addresses1')->references('id')->on('addresses')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('school_id', 'fk_school_addresses_schools1')->references('id')->on('schools')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        /**
        CREATE TABLE IF NOT EXISTS `test_correct`.`school_location_addresses` (
        `address_id` INT(10) UNSIGNED NOT NULL,
        `school_location_id` INT(10) UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `type` VARCHAR(45) NULL DEFAULT NULL,
        INDEX `fk_school_location_addresses_addresses1_idx` (`address_id` ASC),
        INDEX `fk_school_location_addresses_school_locations1_idx` (`school_location_id` ASC),
        PRIMARY KEY (`address_id`, `school_location_id`),
        CONSTRAINT `fk_school_location_addresses_addresses1`
        FOREIGN KEY (`address_id`)
        REFERENCES `test_correct`.`addresses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        CONSTRAINT `fk_school_location_addresses_school_locations1`
        FOREIGN KEY (`school_location_id`)
        REFERENCES `test_correct`.`school_locations` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8
        COLLATE = utf8_unicode_ci;
         */
        Schema::create('school_location_addresses', function(Blueprint $table)
        {
            $table->integer('address_id')->unsigned()->index('fk_school_location_addresses_addresses1_idx');
            $table->integer('school_location_id')->unsigned()->index('fk_school_location_addresses_school_locations1_idx');
            $table->enum('type', ['MAIN', 'INVOICE', 'VISIT', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['address_id', 'school_location_id', 'type'], 'pk_address_id_1');
            $table->foreign('address_id', 'fk_school_location_addresses_addresses1')->references('id')->on('addresses')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('school_location_id', 'fk_school_location_addresses_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
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
