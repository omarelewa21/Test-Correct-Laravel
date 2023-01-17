<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('auto_uwlr_import')->default(0);
            $table->timestamp('auto_uwlr_last_import')->nullable();
            $table->string('auto_uwlr_import_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn(['auto_uwlr_import','auto_uwlr_last_import','auto_uwlr_import_status']);
        });
    }
};
