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
        Schema::create('word_list_word', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_list_id');
            $table->unsignedBigInteger('word_id');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('word_list_id')
                ->references('id')
                ->on('word_lists')
                ->onDelete('cascade');

            $table->foreign('word_id')
                ->references('id')
                ->on('words')
                ->onDelete('cascade');

            // Index to speed up look-ups
            $table->index(['word_list_id', 'word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_list_word');
    }
};
