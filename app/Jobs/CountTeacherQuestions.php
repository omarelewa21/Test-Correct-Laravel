<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\OnboardingWizardReport;
use tcCore\User;

class CountTeacherQuestions extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $count = $this->user->questionAuthors()->count();
        $count = OnboardingWizardReport::getTestsTakenAmount($this->user);
        Log::debug('Teacher #' . $this->user->getKey() . ' -> count_questions: ' . $count);

        $this->user->setAttribute('count_questions', $count);
        $this->user->save();
    }
}
