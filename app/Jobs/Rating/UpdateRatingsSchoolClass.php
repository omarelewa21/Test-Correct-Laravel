<?php

namespace tcCore\Jobs;

use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Rating;
use tcCore\SchoolClass;

class UpdateRatingSchoolClass extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     *
     * @var SchoolClass
     */
    protected $schoolClass;

    /**
     * Create a new job instance.
     *
     * @param SchoolClass $schoolClass
     */
    public function __construct(SchoolClass $schoolClass)
    {
        $this->schoolClass = $schoolClass;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Rating::where('school_class_id', $this->schoolClass->getKey())->update(['education_level_id' => $this->schoolClass->getAttribute('education_level_id'), 'education_level_year' => $this->schoolClass->getAttribute('education_level_year')]);
    }
}