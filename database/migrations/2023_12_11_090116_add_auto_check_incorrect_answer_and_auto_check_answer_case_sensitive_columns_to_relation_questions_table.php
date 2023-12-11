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
        Schema::table('relation_questions', function (Blueprint $table) {
            $table->boolean('auto_check_answer_case_sensitive')->default(false);
            $table->boolean('auto_check_incorrect_answer')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relation_questions', function (Blueprint $table) {
            $table->dropColumn([
                'auto_check_answer_case_sensitive',
                'auto_check_incorrect_answer'
            ]);
        });
    }
};
