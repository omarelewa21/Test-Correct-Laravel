<?php

namespace tcCore\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;

class CountTeacherLastTestTaken extends Job implements ShouldQueue
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
        $user = $this->user;
        // Get teacher's classes
        $teacherIds = $user->teacher()->get(['id', 'class_id', 'subject_id'])->keyBy('id');

        // Get wanted statuses
        $testTakeFinishedStatusIds = TestTakeStatus::whereIn('name', ['Taken', 'Discussing', 'Discussed', 'Rated'])->pluck('id')->all();

        // Get the unique test takes which contain students in the teacher's classes
        $count = TestTake::select('test_takes.id')
            ->where(function ($query) use ($teacherIds) {
                foreach ($teacherIds as $teacherId => $data) {
                    $query->orWhere(function ($query) use ($data) {
                        $query->where('test_participants.school_class_id', $data['class_id'])
                            ->where('tests.subject_id', $data['subject_id']);
                    });
                }
            })
            ->whereIn('test_takes.test_take_status_id', $testTakeFinishedStatusIds)
            ->whereNull('tests.deleted_at')
            ->whereNull('test_participants.deleted_at')
            ->join('tests', 'tests.id', '=', 'test_takes.test_id')
            ->join('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')->max('test_takes.time_start');

        $this->user->setAttribute('count_last_test_taken', $count);
        $this->user->save();
    }
}
