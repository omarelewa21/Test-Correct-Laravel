<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteAttainmentQuery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questionAttainments = \tcCore\QuestionAttainment::where('attainment_id',6004)->get();
        $questionAttainments->each(function($questionAttainment){
            $questionAttainment->delete();
        });
        $attainment = \tcCore\Attainment::find(6004);
        if($attainment){
            $attainment->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $attainment = \tcCore\Attainment::withTrashed()->find(6004);
        if($attainment) {
            $attainment->restore();
            $questionAttainments = \tcCore\QuestionAttainment::withTrashed()->where('attainment_id', 6004)->get();
            $questionAttainments->each(function ($questionAttainment) {
                $questionAttainment->restore();
            });
        }
    }
}
