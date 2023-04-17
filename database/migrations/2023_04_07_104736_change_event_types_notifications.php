<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TestTakeEventType;

return new class extends Migration
{
    protected $silentReasons = ["before-input-meta", "before-input-alt", "started-late", "screenshot", "printscreen"];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $silentReasonCollection = collect($this->silentReasons);

        $silentReasonCollection->each(function ($reason) {
            TestTakeEventType::where("reason", "=", $reason)
                ->update(['show_alarm_to_student' => false, 'requires_confirming' => false]);
        });

        TestTakeEventType::where("reason", "=", "rejoined")->update(['show_alarm_to_student' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $silentReasonCollection = collect($this->silentReasons);
        $silentReasonCollection->each(function ($reason) {
            TestTakeEventType::where("reason", "=", $reason)
                ->update(['show_alarm_to_student' => true, 'requires_confirming' => true]);
        });

        TestTakeEventType::where("reason", "=", "rejoined")->update(['show_alarm_to_student' => true]);
    }
};
