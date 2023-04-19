<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('answer_ratings', function (Blueprint $table) {
            $table->json('json')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('answer_ratings', function (Blueprint $table) {
            $table->dropColumn('json');
        });
    }
};
