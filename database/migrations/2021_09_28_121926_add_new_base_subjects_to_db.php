<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewBaseSubjectsToDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $baseSubjects = [   67 => 'Arabisch',
                            68 => 'Bedrijfseconomie',
                            69 => 'Beeldende vorming',
                            70 => 'Bewegen, Sport en Maatschappij',
                            71 => 'Chinees',
                            72 => 'Culturele en kunstzinnige vorming',
                            73 => 'Drama',
                            74 => 'Dans',
                            75 => 'Informatietechnologie',
                            76 => 'Italiaans',
                            77 => 'Kunst (algemeen)',
                            78 => 'Kunstvakken incl. CKV',
                            79 => 'Lichamelijke opvoeding',
                            80 => 'Lichamelijke opvoeding 2',
                            81 => 'Maatschappijkunde',
                            82 => 'Muziek',
                            83 => 'Russisch',
                            84 => 'Turks',
                            85 => 'Dienstverlening en producten',
                            86 => 'Media, vormgeving en ICT',
                            87 => 'Zorg en welzijn',
                            88 => 'Produceren, installeren en energie',
                            89 => 'Bouwen, wonen en interieur',
                            90 => 'Mobiliteit en transport',
                            91 => 'Horeca, bakkerij en recreatie',
                            92 => 'Groen',
                            93 => 'Economie en ondernemen'
        ];
        foreach ($baseSubjects as $baseSubjectId => $baseSubjectName){
            $baseSubject = \tcCore\BaseSubject::find($baseSubjectId);
            if(!is_null($baseSubject)){
                continue;
            }
            $baseSubject = new \tcCore\BaseSubject();
            $baseSubject->id = $baseSubjectId;
            $baseSubject->name = $baseSubjectName;
            $baseSubject->show_in_onboarding = true;
            $baseSubject->save();

        }

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
