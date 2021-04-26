<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalIdToSchoollocationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_location_user', function (Blueprint $table) {
            $table->string('external_id', 45)->nullable();
//            $table->unique(['school_location_id', 'external_id']); // removed in correspondence with Carlo, should be guarded by the software as we now have some teachers with no external_id, otherwise it would break with the import migration
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_location_user', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
    }
}
