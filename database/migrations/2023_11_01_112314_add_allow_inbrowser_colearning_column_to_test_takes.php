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
            $table->boolean('allow_inbrowser_colearning')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('allow_inbrowser_colearning');
        });
    }
};
