<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->float('score')->change();
        });
        Schema::table('multiple_choice_question_answers', function (Blueprint $table) {
            $table->float('score')->change();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('score')->change();
        });
        Schema::table('multiple_choice_question_answers', function (Blueprint $table) {
            $table->integer('score')->change();
        });
    }
};
