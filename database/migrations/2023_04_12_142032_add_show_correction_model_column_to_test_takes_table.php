<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->boolean('show_correction_model')
                ->default(true)
                ->after('show_grades')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('show_correction_model');
        });
    }
};
