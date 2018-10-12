<?php

namespace tcCore\Jobs\Rating;

use Illuminate\Support\Facades\DB;
use tcCore\AverageRating;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\User;

class CalculateRatingForUser extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var User The user to build statistics for.
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $averages = $this->user->ratings()->select('user_id', 'school_class_id', 'subject_id', DB::raw('SUM(`rating` * `weight`) / SUM(`weight`) AS average'))->groupBy('school_class_id', 'subject_id')->with('schoolClass')->get();

        $averageRatingIds = array();
        foreach($averages as $average) {
            $averageRating = AverageRating::firstOrNew(['user_id' => $average->getAttribute('user_id'), 'school_class_id' => $average->getAttribute('school_class_id'), 'subject_id' => $average->getAttribute('subject_id')]);
            $averageRating->setAttribute('rating', $average->getAttribute('average'));
            $averageRating->setAttribute('deleted_at', null);
            $averageRating->save();
            $averageRatingIds[] = $averageRating->getKey();
        }

        AverageRating::where('user_id', $this->user->getKey())->whereNotIn('id', $averageRatingIds)->delete();
    }
}
