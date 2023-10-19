<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('relation_question_word', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->integer('word_id')
                ->foreign('word_id')
                ->references('id')
                ->on('words');
            $table->integer('relation_question_id')
                ->foreign('relation_question_id')
                ->references('id')
                ->on('relation_questions');
            $table->integer('word_list_id')
                ->foreign('word_list_id')
                ->references('id')
                ->on('word_lists');

            $table->boolean('selected')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relation_question_word');
    }
};
