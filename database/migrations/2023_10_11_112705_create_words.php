<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique();

            $table->string('text');
            $table->unsignedBigInteger('word_id')
                ->nullable()
                ->foreign('word_id')
                ->references('id')
                ->on('words')
                ->onDelete('cascade');

            $table->string('type');
            $table->integer('user_id')->foreign('user_id')->references('id')->on('users');

            $table->integer('subject_id')->foreign('subject_id')->references('id')->on('subjects');
            $table->integer('education_level_id')->foreign('education_level_id')->references('id')->on('education_levels');
            $table->integer('education_level_year');
            $table->integer('school_location_id')->foreign('school_location_id')->references('id')->on('school_locations');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
