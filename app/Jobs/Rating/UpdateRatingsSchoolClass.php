<?php

namespace tcCore\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use tcCore\Rating;
use tcCore\SchoolClass;

class UpdateRatingSchoolClass extends Job implements ShouldQueue
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