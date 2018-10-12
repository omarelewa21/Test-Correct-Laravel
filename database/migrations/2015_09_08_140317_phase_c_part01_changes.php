<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseCPart01Changes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_values', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->decimal('score', 11, 1)->unsigned();
            $table->decimal('max_score', 11, 1)->unsigned();
            $table->integer('answer_id')->unsigned()->index('fk_p_values_answers1_idx');
            $table->integer('test_participant_id')->unsigned()->index('fk_p_values_test_participants1_idx');
            $table->integer('question_id')->unsigned()->index('fk_p_values_questions1_idx');
            $table->integer('period_id')->unsigned()->index('fk_p_values_periods1_idx');
            $table->integer('school_class_id')->unsigned()->index('fk_p_values_school_classes1_idx');
            $table->integer('education_level_id')->unsigned()->index('fk_p_values_education_levels1_idx');
            $table->integer('education_level_year')->unsigned();
            $table->integer('subject_id')->unsigned()->index('fk_p_values_subjects1_idx');
            $table->foreign('answer_id', 'fk_p_values_answers1')->references('id')->on('answers')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('test_participant_id', 'fk_p_values_test_participants1')->references('id')->on('test_participants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('question_id', 'fk_p_values_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('period_id', 'fk_p_values_periods1')->references('id')->on('periods')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('school_class_id', 'fk_p_values_school_classes1')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('education_level_id', 'fk_p_values_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('subject_id', 'fk_p_values_subjects1')->references('id')->on('subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::create('p_value_users', function(Blueprint $table)
        {
            $table->integer('p_value_id')->unsigned()->index('fk_p_value_users_p_values1_idx');
            $table->integer('user_id')->unsigned()->index('fk_p_value_users_users1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['p_value_id', 'user_id'], '');
            $table->foreign('p_value_id', 'fk_p_value_users_p_values1')->references('id')->on('p_values')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id', 'fk_p_value_users_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::create('p_value_attainments', function(Blueprint $table)
        {
            $table->integer('p_value_id')->unsigned()->index('fk_p_value_attainments_p_values1_idx');
            $table->integer('attainment_id')->unsigned()->index('fk_p_value_attainments_attainments1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['p_value_id', 'attainment_id'], '');
            $table->foreign('p_value_id', 'fk_p_value_attainments_p_values1')->references('id')->on('p_values')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('attainment_id', 'fk_p_value_attainments_attainments1')->references('id')->on('attainments')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::create('ratings', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->decimal('rating', 8, 4)->unsigned();
            $table->decimal('score', 11, 1)->unsigned();
            $table->decimal('max_score', 11, 1)->unsigned();
            $table->integer('weight')->unsigned();
            $table->integer('test_participant_id')->unsigned()->index('fk_ratings_test_participants1_idx');
            $table->integer('user_id')->unsigned()->index('fk_ratings_users1_idx');
            $table->integer('period_id')->unsigned()->index('fk_ratings_periods1_idx');
            $table->integer('school_class_id')->unsigned()->index('fk_ratings_school_classes1_idx');
            $table->integer('education_level_id')->unsigned()->index('fk_ratings_education_levels1_idx');
            $table->integer('education_level_year')->unsigned();
            $table->integer('subject_id')->unsigned()->index('fk_ratings_subjects1_idx');
            $table->foreign('test_participant_id', 'fk_ratings_test_participants1')->references('id')->on('test_participants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id', 'fk_ratings_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('period_id', 'fk_ratings_periods1')->references('id')->on('periods')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('school_class_id', 'fk_ratings_school_classes1')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('education_level_id', 'fk_ratings_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('subject_id', 'fk_ratings_subjects1')->references('id')->on('subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::create('average_ratings', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();

            $table->decimal('rating', 8, 4)->unsigned();

            $table->integer('user_id')->unsigned()->index('fk_average_ratings_users1_idx');
            $table->integer('school_class_id')->unsigned()->index('fk_average_ratings_school_classes1_idx');
            $table->integer('subject_id')->unsigned()->index('fk_average_ratings_subjects1_idx');

            $table->foreign('user_id', 'fk_average_ratings_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('school_class_id', 'fk_average_ratings_school_classes1')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('subject_id', 'fk_average_ratings_subjects1')->references('id')->on('subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('average_ratings');
        Schema::drop('ratings');
        Schema::drop('p_value_attainments');
        Schema::drop('p_value_users');
        Schema::drop('p_values');
    }
}
