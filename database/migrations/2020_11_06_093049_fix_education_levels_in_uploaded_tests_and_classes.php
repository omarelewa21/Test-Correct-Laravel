<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\FileManagement;

class FixEducationLevelsInUploadedTestsAndClasses extends Migration
{
    protected $schoolEducationLevels;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        FileManagement::where('created_at','>=','2020-09-15 00:00:01')->orderBy('school_location_id')->get()->each(function(FileManagement $f){
            $typedetails = $f->typedetails;
            if(Uuid::isValid($typedetails->education_level_id)) { // we need to transform the uuid into a regular integer id
              $schoolEducationLevel = $this->getSchoolEducationLevelBySchoolLocationIdAndUuid($f->school_location_id,$typedetails->education_level_id);
              $typedetails->education_level_id = $schoolEducationLevel->getKey();
            }
            $f->typedetails = $typedetails;
            $f->save();
        });
    }

    protected function getSchoolEducationLevelBySchoolLocationIdAndUuid($schoolLocationId, $uuid)
    {
        if(null === $this->schoolEducationLevels){
            $this->schoolEducationLevels = EducationLevel::all();

        }
        return $this->schoolEducationLevels->first(function(EducationLevel $s) use ($uuid){
            return $s->uuid == $uuid;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
