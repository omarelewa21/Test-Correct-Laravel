<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\BaseSubject;

class AddNewBaseSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        collect([
            [
                'id' => 94,
                'name' => 'Maatschappijleer 2',
            ],
            [
                'id' => 95,
                'name'=> 'Nask1',
            ],
            [
                'id' => 96,
                'name' => 'Nask2',
            ]
        ])->each(function($data){
            $baseSubject = new BaseSubject();
            $baseSubject->id = $data['id'];
            $baseSubject->name = $data['name'];
            $baseSubject->show_in_onboarding = 1;
            $baseSubject->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        BaseSubject::whereIn('id',[94,95,96])->forceDelete();
    }
}
