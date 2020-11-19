<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolLocationSharedSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_location_shared_sections', function (Blueprint $table) {
            $table->biginteger('school_location_id');
            $table->biginteger('section_id');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['school_location_id','section_id'],'school_location_school_shared_section_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_location_shared_sections');
    }
}
