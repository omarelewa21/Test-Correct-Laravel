<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixingLaravelsShortcomings1 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('ALTER TABLE `school_years` CHANGE COLUMN `year` `year` INT(4) UNSIGNED NULL DEFAULT NULL;');
        DB::unprepared('ALTER TABLE `test_participants` CHANGE COLUMN `ip_address` `ip_address` VARBINARY(16) NULL;');
        DB::unprepared('ALTER TABLE `answers` CHANGE COLUMN `note` `note` LONGBLOB NULL DEFAULT NULL;');
        DB::unprepared('ALTER TABLE `drawing_questions` CHANGE COLUMN `answer` `answer` LONGBLOB NULL DEFAULT NULL;');
        DB::unprepared('ALTER TABLE `school_location_ips` CHANGE COLUMN `ip` `ip` VARBINARY(16) NOT NULL, CHANGE COLUMN `netmask` `netmask` INT(3) UNSIGNED NOT NULL;');
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('ALTER TABLE `school_years` CHANGE COLUMN `year` `year` INT(10) UNSIGNED NULL DEFAULT NULL;');
        DB::unprepared('ALTER TABLE `test_participants` CHANGE COLUMN `ip_address` `ip_address` BLOB NULL;');
        DB::unprepared('ALTER TABLE `answers` CHANGE COLUMN `note` `note` BLOB NULL DEFAULT NULL;');
        DB::unprepared('ALTER TABLE `drawing_questions` CHANGE COLUMN `answer` `answer` BLOB NULL DEFAULT NULL;');
        DB::unprepared('ALTER TABLE `school_location_ips` CHANGE COLUMN `ip` `ip` BLOB NOT NULL, CHANGE COLUMN `netmask` `netmask` INT(10) UNSIGNED NOT NULL;');
    }

}