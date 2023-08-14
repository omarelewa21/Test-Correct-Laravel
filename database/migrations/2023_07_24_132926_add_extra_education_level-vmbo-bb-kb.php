<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\EducationLevel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            if(!\tcCore\EducationLevel::where('name','Vmbo bb/vmbo kb')->exists()){
                collect([
                    [
                        'id' => 34,
                        'name'=> 'Vmbo bb/Vmbo kb',
                        'max_years' => 4,
                        'attainment_education_level_id' => 6,
                        'min_attainment_year' => 3
                    ],

                ])->each(function($data){
                    $el = new EducationLevel();
                    foreach($data as $key => $value){
                        $el->$key = $value;
                    }
                    $el->save();
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        EducationLevel::where('name','Vmbo bb/Vmbo kb')->forceDelete();
    }
};
