<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TestTakeStatusLog;

class RemoveNoRealDiscussionTestTakeStatusLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TestTakeStatusLog::where('test_take_status_id',8)->get()->each(function(TestTakeStatusLog $t){
          TestTakeStatusLog::where('test_take_id',$t->test_take_id)->where('test_take_status_id',7)->where('created_at','>=',$t->created_at->subSeconds(120))->delete();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
