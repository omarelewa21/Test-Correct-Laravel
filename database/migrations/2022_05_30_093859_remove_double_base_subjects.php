<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\BaseSubject;
use tcCore\Subject;

class RemoveDoubleBaseSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // update subjects with base_subject_id = 94 => 29
        Subject::where('base_subject_id',94)->update(['base_subject_id' => 29]);
        // update subjects with base_subject_id = 95 => 27
        Subject::where('base_subject_id',95)->update(['base_subject_id' => 27]);
        // update subjects with base_subject_id = 96 => 28
        Subject::where('base_subject_id',96)->update(['base_subject_id' => 28]);
        // rename BaseSubject (ML2) = 29 => Maatschapijleer 2
        BaseSubject::where('id',29)->update(['name' => 'Maatschappijleer 2']);
        // remove BaseSubjects with id 94, 95 & 96
        BaseSubject::whereIn('id',[94,95,96])->forceDelete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        (new AddNewBaseSubjects())->up();
    }
}
