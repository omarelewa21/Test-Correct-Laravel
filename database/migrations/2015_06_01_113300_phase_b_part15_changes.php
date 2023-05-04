<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart15Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('school_location_education_levels', function(Blueprint $table)
        {
            $table->integer('school_location_id')->unsigned()->index('fk_school_location_education_levels_school_locations1_idx');
            $table->integer('education_level_id')->unsigned()->index('fk_school_location_education_levels_education_levels1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_location_id', 'education_level_id'], 'pk_school_location_id_2');
            $table->foreign('school_location_id', 'fk_school_location_education_levels_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('education_level_id', 'fk_school_location_education_levels_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::create('grading_scales', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('name', 45);
            $table->string('system_name', 45);
        });

        $gradeId = DB::table('grading_scales')->insertGetId(
            ['created_at' => 'NOW()', 'updated_at' => 'NOW()', 'deleted_at' => null, 'name' => 'Nederlands', 'system_name' => 'OneToTen']
        );

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->integer('grading_scale_id')->unsigned()->index('fk_school_locations_grading_scales1_idx')->after('school_id');
            $table->boolean('activated')->default(0);
        });

        DB::table('school_locations')
            ->update(['grading_scale_id' => $gradeId]);

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->foreign('grading_scale_id', 'fk_school_locations_grading_scales1')->references('id')->on('grading_scales')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('users', function (Blueprint $table)
        {
            $table->boolean('time_dispensation')->default(0)->after('gender');
            $table->boolean('send_welcome_email')->default(0)->after('time_dispensation');
        });

        Schema::create('student_parents', function(Blueprint $table)
        {
            $table->integer('parent_id')->unsigned()->index('fk_student_parents_parents1_idx');
            $table->integer('user_id')->unsigned()->index('fk_student_parents_users1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['parent_id', 'user_id'], '');
            $table->foreign('parent_id', 'fk_student_parents_parents1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id', 'fk_student_parents_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('school_location_education_levels');
        Schema::drop('grading_scales');

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->dropForeign('fk_school_locations_grading_scales1');
            $table->dropIndex('fk_school_locations_grading_scales1_idx');
            $table->dropColumn('grading_scale_id');
            $table->dropColumn('activated');
        });

        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn('time_dispensation');
            $table->dropColumn('send_welcome_email');
        });

        Schema::drop('parents');
    }
}
