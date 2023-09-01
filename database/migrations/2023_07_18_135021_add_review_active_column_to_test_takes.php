<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\Answer;
use tcCore\TestParticipant;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->boolean('review_active')->default(false);
            $table->dateTime('results_published')->nullable();
        });

        \DB::table('test_takes')
            ->whereNotNull('show_results')
            ->update(['review_active' => true]);
        
        \tcCore\TestTake::where('test_take_status_id', \tcCore\TestTakeStatus::STATUS_RATED)
            ->chunkById(100, function ($testTakes) {
                foreach ($testTakes as $testTake) {
                    $hasFinalRating = Answer::whereIn(
                        'test_participant_id',
                        TestParticipant::where('test_take_id', $testTake->id)->select('id')
                    )
                        ->whereNotNull('final_rating')
                        ->doesntExist();
                    if ($hasFinalRating) {
                        \DB::table('test_takes')
                            ->where('id', $testTake->id)
                            ->update(['results_published' => $testTake->updated_at]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn(['review_active', 'results_published']);
        });
    }
};
