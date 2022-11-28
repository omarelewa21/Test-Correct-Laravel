<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZoomGroupColumnToDrawingQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drawing_questions', function (Blueprint $table) {
            $table->json('zoom_group')->nullable()->after('grid_svg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drawing_questions', function (Blueprint $table) {
            $table->dropColumn('zoom_group');
        });
    }
}
