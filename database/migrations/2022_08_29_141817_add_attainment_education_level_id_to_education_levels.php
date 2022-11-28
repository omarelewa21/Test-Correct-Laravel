<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use tcCore\EducationLevel;

class AddAttainmentEducationLevelIdToEducationLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        try {
            if (!Schema::hasColumn('education_levels', 'attainment_education_level_id')) {
                Schema::table('education_levels', function (Blueprint $table) {
                    $table->integer('attainment_education_level_id')->nullable();
                });
            }
            \DB::statement('UPDATE education_levels SET attainment_education_level_id = id where attainment_education_level_id is null');
            $havoId = EducationLevel::where('name','Havo')->value('id');
            EducationLevel::where('name','Havo/VWO')->update(['attainment_education_level_id' => $havoId]);
            $mavoId = EducationLevel::where('name','Mavo / Vmbo tl')->value('id');
            EducationLevel::where('name','Mavo/Havo')->update(['attainment_education_level_id' => $mavoId]);
            DB::commit();
        } catch (Throwable $e){
            DB::rollBack();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->dropColumn(['attainment_education_level_id']);
        });
    }
}
