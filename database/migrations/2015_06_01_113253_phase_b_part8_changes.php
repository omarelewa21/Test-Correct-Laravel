<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart8Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->text('note', 65535)->nullable()->after('gender');
        });

        /**
        ALTER TABLE `test_correct`.`umbrella_organizations`
        ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL AFTER `deleted_at`,
        ADD INDEX `fk_umbrella_organizations_users1_idx` (`user_id` ASC)
        ALTER TABLE `test_correct`.`umbrella_organizations`
        ADD CONSTRAINT `fk_umbrella_organizations_users1`
        FOREIGN KEY (`user_id`)
        REFERENCES `test_correct`.`users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
         */
        Schema::table('umbrella_organizations', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned()->index('fk_umbrella_organizations_users1_idx')->after('deleted_at');
        });

        Schema::table('schools', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned()->index('fk_schools_users1_idx')->after('deleted_at');
        });

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned()->index('fk_school_locations_users1_idx')->after('deleted_at');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('email', 150)->after('mobile');
        });

        Schema::table('umbrella_organizations', function (Blueprint $table) {
            $table->string('main_address', 60)->after('name');
            $table->string('main_postal', 7)->after('main_address');
            $table->string('main_city', 60)->after('main_postal');
            $table->string('main_country', 60)->after('main_city');
            $table->string('invoice_address', 60)->after('main_country');
            $table->string('invoice_postal', 7)->after('invoice_address');
            $table->string('invoice_city', 60)->after('invoice_postal');
            $table->string('invoice_country', 60)->after('invoice_city');

        });

        Schema::table('schools', function (Blueprint $table) {
            $table->string('main_address', 60)->after('name');
            $table->string('main_postal', 7)->after('main_address');
            $table->string('main_city', 60)->after('main_postal');
            $table->string('main_country', 60)->after('main_city');
            $table->string('invoice_address', 60)->after('main_country');
            $table->string('invoice_postal', 7)->after('invoice_address');
            $table->string('invoice_city', 60)->after('invoice_postal');
            $table->string('invoice_country', 60)->after('invoice_city');
            $table->integer('umbrella_organization_id')->unsigned()->nullable()->change();
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->string('main_address', 60)->after('name');
            $table->string('main_postal', 7)->after('main_address');
            $table->string('main_city', 60)->after('main_postal');
            $table->string('main_country', 60)->after('main_city');
            $table->string('invoice_address', 60)->after('main_country');
            $table->string('invoice_postal', 7)->after('invoice_address');
            $table->string('invoice_city', 60)->after('invoice_postal');
            $table->string('invoice_country', 60)->after('invoice_city');
            $table->string('visit_address', 60)->after('invoice_country');
            $table->string('visit_postal', 7)->after('visit_address');
            $table->string('visit_city', 60)->after('visit_postal');
            $table->string('visit_country', 60)->after('visit_city');
            $table->integer('school_id')->unsigned()->nullable()->change();
        });

        DB::table('umbrella_organizations')
            ->where('user_id', 0)
            ->update(['user_id' => DB::table('users')->value('id')]);

        Schema::table('umbrella_organizations', function(Blueprint $table)
        {
            $table->foreign('user_id', 'fk_umbrella_organizations_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        DB::table('schools')
            ->where('user_id', 0)
            ->update(['user_id' => DB::table('users')->value('id')]);

        Schema::table('schools', function(Blueprint $table)
        {
            $table->foreign('user_id', 'fk_schools_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        DB::table('school_locations')
            ->where('user_id', 0)
            ->update(['user_id' => DB::table('users')->value('id')]);

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->foreign('user_id', 'fk_school_locations_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::drop('school_location_subjects');

        Schema::drop('school_location_periods');

        Schema::create('school_location_sections', function(Blueprint $table)
        {
            $table->integer('school_location_id')->unsigned()->index('fk_school_location_sections_school_locations1_idx');
            $table->integer('section_id')->unsigned()->index('fk_school_location_sections_sections1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_location_id', 'section_id'], '');
            $table->foreign('school_location_id', 'fk_school_location_sections_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('section_id', 'fk_school_location_sections_sections1')->references('id')->on('sections')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::create('school_location_school_years', function(Blueprint $table)
        {
            $table->integer('school_location_id')->unsigned()->index('fk_school_location_school_years_school_locations1_idx');
            $table->integer('school_year_id')->unsigned()->index('fk_school_location_school_years_school_years1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_location_id', 'school_year_id'], 'pk_school_location_id_1');
            $table->foreign('school_location_id', 'fk_school_location_school_years_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('school_year_id', 'fk_school_location_school_years_sections1')->references('id')->on('school_years')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign('fk_teachers_school_years1');
            $table->dropColumn('school_year_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('section_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }

}
