<?php

namespace tcCore\Jobs\PValues;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use tcCore\Answer;
use tcCore\Jobs\Job;
use tcCore\PValue;
use tcCore\QuestionAttainment;
use tcCore\Teacher;

class CalculatePValueForAnswer extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Answer The answer to build statistics for.
     */
    protected $answer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $finalRating = $this->answer->calculateFinalRating();
        dump($finalRating);
        if ($finalRating === null) {
            PValue::where('answer_id', $this->answer->getKey())->delete();
            return;
        }
        $pvalueData = array();
        $pvalueData['score'] = $finalRating;
 
        $pValue = PValue::firstOrNew(['answer_id' => $this->answer->getKey()]);

        $question = $this->answer->question;
        if (is_null($question)) {
            throw new \Exception('Answer does not belong to a question. This should NOT be possible!');
        }
        $pvalueData['question_id'] = $question->getKey();
        $pvalueData['max_score'] = $question->getAttribute('score');

        $testParticipant = $this->answer->testParticipant;
        if (is_null($testParticipant)) {
            throw new \Exception('Answer does not belong to a test participant. This should NOT be possible!');
        }

        $pvalueData['test_participant_id'] = $testParticipant->getKey();

        $testTake = $testParticipant->testTake;
        if (is_null($testTake)) {
            throw new \Exception('Test participant does not belong to a test take. This should NOT be possible!');
        }

        $period = $testTake->period;
        if (is_null($period)) {
            throw new \Exception('Test take does not belong to a period. This should NOT be possible!');
        }

        $pvalueData['period_id'] = $period->getKey();

        $test = $testTake->test;
        if (is_null($test)) {
            throw new \Exception('Test take does not belong to a test. This should NOT be possible!');
        }

        $subject = $test->subject;
        if (is_null($subject)) {
            throw new \Exception('Test does not belong to a subject. This should NOT be possible!');
        }

        $pvalueData['subject_id'] = $subject->getKey();

        $schoolClass = $testParticipant->schoolClass;
        if (is_null($schoolClass)) {
            throw new \Exception('Test participant does not belong to a school class. This should NOT be possible!');
        }

        $pvalueData['school_class_id'] = $schoolClass->getKey();

        $educationLevel = $schoolClass->educationLevel;
        if (is_null($educationLevel)) {
            throw new \Exception('School class does not belong to a education level. This should NOT be possible!');
        }

        $pvalueData['education_level_id'] = $educationLevel->getKey();
        $pvalueData['education_level_year'] = $schoolClass->getAttribute('education_level_year');

        $pvalueData['users'] = Teacher::where('subject_id', $subject->getKey())->where('class_id', $schoolClass->getKey())->pluck('user_id')->all();
        $pvalueData['attainments'] = QuestionAttainment::where('question_id', $question->getKey())->pluck('attainment_id')->all();

        $pValue->fill($pvalueData);

        if ($pValue->save() !== true) {
            throw new \Exception('Failed to save p-value!');
        }

    }
}
