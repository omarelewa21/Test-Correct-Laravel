<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBaseSubjectNovaVerpleegkundeToDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $baseSubjects = [
            94 => 'Verpleegkunde NOVA Haarlem'
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
        Schema::table('db', function (Blueprint $table) {
            //
        });
    }
}
