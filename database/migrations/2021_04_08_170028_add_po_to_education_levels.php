<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\EducationLevel;

class AddPoToEducationLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('education_levels', function (Blueprint $table) {
            if(EducationLevel::where('name','Groep')->count() === 0){
                EducationLevel::Create([
                    'name' => 'Groep',
                    'max_years' => 8
                ]);
            }
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
            EducationLevel::where('name','Groep')->forceDelete();
        });
    }
}
