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
        Schema::table('school_locations', function (Blueprint $table) {
            $table->integer('import_merge_school_location_id')->nullable()->default(null)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn(['import_merge_school_location_id']);
        });
    }
};
