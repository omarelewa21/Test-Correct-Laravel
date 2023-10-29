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
        Schema::table('test_takes', function (Blueprint $table) {
            $table->boolean('enable_answer_model_colearning')->default(false);
            $table->boolean('enable_question_text_colearning')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('enable_answer_model_colearning');
            $table->dropColumn('enable_question_text_colearning');
        });
    }
};
