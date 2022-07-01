<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\BaseSubject;

class AddGodsdienstAndLevensbeschouwelijkVormingsonderwijsToBaseSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('base_subjects', function (Blueprint $table) {
            collect([
                [
                    'id' => 95,
                    'name'=> 'Godsdienst',
                ],
                [
                    'id' => 96,
                    'name' => 'Levensbeschouwelijk vormingsonderwijs',
                ],
                [
                    'id' => 97,
                    'name' => 'Overige',
                ]
            ])->each(function($data){
                $baseSubject = new BaseSubject();
                $baseSubject->id = $data['id'];
                $baseSubject->name = $data['name'];
                $baseSubject->show_in_onboarding = 1;
                $baseSubject->save();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('base_subjects', function (Blueprint $table) {
            BaseSubject::whereIn('id',[95,96,97])->forceDelete();
        });
    }
}
