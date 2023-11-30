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
            $table->boolean('shuffle_per_participant')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relation_questions', function (Blueprint $table) {
            $table->dropColumn('shuffle_per_participant');
        });
    }
};
