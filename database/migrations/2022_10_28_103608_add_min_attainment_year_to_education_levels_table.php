<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\EducationLevel;

class AddMinAttainmentYearToEducationLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->integer('min_attainment_year')->nullable();

        });
        collect([
            ['Atheneum', 4],
            ['VWO', 4],
            ['Gymnasium', 4],
            ['Havo', 4],
            ['Mavo / VMBO tl', 3],
            ['Vmbo gl', 3],
            ['Vmbo kb', 3],
            ['Vmbo bb', 3],
            ['Havo/VWO', 3],
            ['Mavo/Havo', 2],
        ])->each(function ($item) {
            $educationLevel = EducationLevel::firstWhere('name', $item[0]);
            $educationLevel->min_attainment_year = $item[1];
            $educationLevel->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->dropColumn('min_attainment_year');
        });
    }
}
