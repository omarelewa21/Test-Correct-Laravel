<?php

namespace tcCore\Jobs\Rating;

use Illuminate\Support\Facades\Log;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Lib\Repositories\AnswerRepository;
use tcCore\Rating;
use tcCore\TestParticipant;

class CalculateRatingForTestParticipant extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var TestParticipant The testparticipant to build statistics for.
     */
    protected $testParticipant;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TestParticipant $testParticipant)
    {
        $this->testParticipant = $testParticipant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $testTake = $this->testParticipant->testTake()->withTrashed()->first();
        if (is_null($testTake)) {
            throw new \Exception('Test participant does not belong to a test take. This should NOT be possible!');
        }

        // Re-takes will set rating in retake_rating, so ignore
        if ($testTake->getAttribute('retake_test_take_id') || ($this->testParticipant->getAttribute('rating') === null && $this->testParticipant->getAttribute('retake_rating') === null)) {
            Rating::where('test_participant_id', $this->testParticipant->getKey())->delete();
            return;
        }

        $ratingData = array();
        $rating = Rating::firstOrNew(['test_participant_id' => $this->testParticipant->getKey()]);

        $testParticipantRating = $this->testParticipant->getAttribute('rating');
        if ($testParticipantRating === null || $testParticipantRating < $this->testParticipant->getAttribute('retake_rating')) {
            $testParticipantRating = $this->testParticipant->getAttribute('retake_rating');
        }

        $testParticipantScore = AnswerRepository::getTestParticipantScores($this->testParticipant);
        $testParticipantMaxScore = $testParticipantScore->max;
        $testParticipantScore = $testParticipantScore->current;

        $ratingData['rating'] = $testParticipantRating;
        $ratingData['score'] = $testParticipantScore;
        $ratingData['max_score'] = $testParticipantMaxScore;
        $ratingData['weight'] = $testTake->getAttribute('weight');

        $user = $this->testParticipant->user;
        if (is_null($user)) {
            throw new \Exception('Test participant does not belong to a user. This should NOT be possible!');
        }

        $ratingData['user_id'] = $user->getKey();

        $period = $testTake->period;
        if (is_null($period)) {
            throw new \Exception('Test take does not belong to a period. This should NOT be possible!');
        }

        $ratingData['period_id'] = $period->getKey();

        $test = $testTake->test;
        if (is_null($test)) {
            throw new \Exception('Test take does not belong to a test. This should NOT be possible!');
        }

        $subject = $test->subject;
        if (is_null($subject)) {
            throw new \Exception('Test does not belong to a subject. This should NOT be possible!');
        }

        $ratingData['subject_id'] = $subject->getKey();

        $schoolClass = $this->testParticipant->schoolClass;
        if (is_null($schoolClass)) {
            throw new \Exception('Test participant does not belong to a school class. This should NOT be possible!');
        }

        $ratingData['school_class_id'] = $schoolClass->getKey();

        $educationLevel = $schoolClass->educationLevel;
        if (is_null($educationLevel)) {
            throw new \Exception('School class does not belong to a education level. This should NOT be possible!');
        }

        $ratingData['education_level_id'] = $educationLevel->getKey();
        $ratingData['education_level_year'] = $schoolClass->getAttribute('education_level_year');

        $rating->fill($ratingData);

        if($rating->save() !== true) {
            throw new \Exception('Failed to save rating!');
        }
    }
}
