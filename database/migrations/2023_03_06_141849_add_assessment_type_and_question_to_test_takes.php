<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->tinyText('assessment_type')->nullable();
            $table->integer('assessing_question_id')->nullable();
            $table->dateTime('assessed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn(['assessment_type', 'assessing_question_id', 'assessed_at']);
        });
    }
};