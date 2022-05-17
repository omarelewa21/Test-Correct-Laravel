<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('abbreviation')->nullable();
            $table->bigInteger('base_subject_id')->nullable();
            $table->bigInteger('default_section_id');
            $table->string('education_levels')->nullable();
            $table->boolean('demo')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('default_subjects');
    }
}
