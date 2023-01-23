<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;
use tcCore\TestTake;

class CoLearning extends Component
{
    const DISCUSSION_TYPE_ALL = 'ALL';
    const DISCUSSION_TYPE_OPEN_ONLY = 'OPEN_ONLY';

    //TestTake properties
    public int|TestTake $testTake;
    public bool $showStartOverlay = true;

    public bool $openOnly;

    //TestParticipant properties
    public $testParticipantStatusses;
    public float $testParticipantsFinishedWithRatingPercentage; //if 100.0, all possible answers have been rated //todo float or int?

    public int $testParticipantCount;
    public int $testParticipantCountActive;

    //Question Navigation properties
    public int $firstQuestionId;
    public int $lastQuestionId;

    public int $questionCount;
    public int $questionCountOpenOnly;

    public int $questionIndex;
    public int $questionIndexOpenOnly;

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;

        if ($test_take->discussing_question_id === null) {
            //gets a testTake, but misses TestParticipants Status data.
            // also creates AnswerRatings for students.
            //todo Remove unnecesairy TestTake Query? merge query actions? nextQuestion and participant activity data
            (new TestTakesController)->nextQuestion($test_take);
        }

    }

    public function render()
    {
        $this->openOnly = $this->testTake->discussion_type === self::DISCUSSION_TYPE_OPEN_ONLY;

        $this->getTestParticipantsData();

        $this->handleTestParticipantStatusses(); //todo move to polling method

        $this->getNavigationData();

        return view('livewire.teacher.co-learning')
            ->layout('layouts.co-learning-teacher');
    }

    public function nextDiscussionQuestion()
    {
        //do i have to refresh/fresh the TestTake before passing it to the controller?
        (new TestTakesController)->nextQuestion($this->testTake);
    }

    /* start header methods */
    public function redirectBack()
    {
        return redirect()->route('teacher.test-takes', ['stage' => 'taken']);
    }


    public function finishCoLearning()
    {
        $this->handleFinishingCoLearning();

        return redirect()->route('teacher.test-takes', ['stage' => 'taken', 'tab' => 'norm']);
    }
    /* end header methods */

    /* start sidebar methods */
    public function goToNextQuestion()
    {
        /*TODO
         * check => make all students go to the next question
         */
        //todo check if all students have rated their AnswerRatings?
        if ($this->testParticipantsFinishedWithRatingPercentage === 100) {
            return $this->nextDiscussionQuestion();
        }

        //if not all answerRatings have been Rated, open Modal for confirmation? then the modal can call $this->nextDiscussionQuestion() or something?
        return $this->nextDiscussionQuestion();
    }

    public function goToPreviousQuestion()
    {
        /*TODO
         * NEW FUNCTIONALITY! DOESNT WORK WITH OLD CO_LEARNING CONTROLLERS!
         * Get the previous Question, make all students go to the previous question
         */
    }

    public function showStudentAnswer($uuidOrId)
    {
        //TODO show the answer, the student is now rating, on the teacher screen.
    }

    /* end sidebar methods */

    public function getAtLastQuestionProperty()
    {
        return $this->testTake->discussing_question_id === $this->lastQuestionId;
    }

    public function getAtFirstQuestionProperty()
    {
        return $this->testTake->discussing_question_id === $this->firstQuestionId;
    }

    private function handleFinishingCoLearning()
    {
        $this->testTake->update([
            'test_take_status_id' => 8,
            'skipped_discussion'  => false,
        ]);
    }

    private function handleTestParticipantStatusses(): void
    {
        //reset values
        $this->testParticipantStatusses = collect();

        $testParticipantsCount = $this->testTake->testParticipants->sum(fn($tp) => $tp->active === true);
        $testParticipantsFinishedWithRatingCount = $this->testTake->testParticipants->sum(fn($tp) => ($tp->answer_to_rate === $tp->answer_rated) && $tp->active);

        $this->testParticipantsFinishedWithRatingPercentage = $testParticipantsCount > 0
            ? $testParticipantsFinishedWithRatingCount / $testParticipantsCount * 100
            : 0;

        $this->testTake->testParticipants->each(function ($testParticipant) {
            if (!$testParticipant->active) {
                return;
            }
            if(!isset($testParticipant->answer_to_rate) || $testParticipant->answer_to_rate === null) {
                return;
            }

            $testParticipantPercentageRated = $testParticipant->answer_to_rate === 0
                ? 0
                : ($testParticipant->answer_rated / $testParticipant->answer_to_rate) * 100;

            $this->testParticipantStatusses[$testParticipant->uuid] = [
                'ratingStatus' => $this->getRatingStatusForTestParticipant($testParticipantPercentageRated)
            ];
        });

        $abnormalitiesTotal = $this->testTake->testParticipants->sum(fn($tp) => ($tp->active && isset($tp->abnormalities)) ? $tp->abnormalities : 0);
        $abnormalitiesCount = $this->testTake->testParticipants->sum(fn($tp) => $tp->active && isset($tp->abnormalities));
        $abnormalitiesAverage = ($abnormalitiesCount === 0) ? 0 : $abnormalitiesTotal / $abnormalitiesCount;

        $this->testTake->testParticipants->each(function ($testParticipant) use (&$abnormalitiesAverage) {
            if (!$testParticipant->active) {
                return;
            }
            if(!isset($testParticipant->answer_to_rate) || $testParticipant->answer_to_rate === null) {
                return;
            }

            $testParticipantAbnormalitiesAverageDeltaPercentage = null;

            if ($testParticipant->answer_rated > 0 && $abnormalitiesAverage == 0 && $testParticipant->active) {
                $testParticipantAbnormalitiesAverageDeltaPercentage = 100;
            }

            if ($abnormalitiesAverage != 0 && $testParticipant->active) {
                $testParticipantAbnormalitiesAverageDeltaPercentage = (100 / $abnormalitiesAverage) * $testParticipant->abnormalities;
            }

            $this->testParticipantStatusses = $this->testParticipantStatusses->mergeRecursive([
                $testParticipant->uuid => [
                    'abnormalitiesStatus' => $this->getAbnormalitiesStatusForTestParticipant($testParticipantAbnormalitiesAverageDeltaPercentage)
                ]
            ]);

        });
    }

    function getAbnormalitiesStatusForTestParticipant($averageDeltaPercentage): AbnormalitiesStatus
    {
        if ($averageDeltaPercentage === null) {
            return AbnormalitiesStatus::Default;
        }

        if ($averageDeltaPercentage < 95) {
            return AbnormalitiesStatus::Happy;
        }
        if ($averageDeltaPercentage < 115) {
            return AbnormalitiesStatus::Neutral;
        }
        return AbnormalitiesStatus::Sad;
    }

    function getRatingStatusForTestParticipant($percentageRated): RatingStatus
    {
        if ($percentageRated === 100) {
            return RatingStatus::Green;
        }
        if (
            $percentageRated < 50 &&
            $this->testParticipantsFinishedWithRatingPercentage > 50
        ) {
            return RatingStatus::Red;
        }
        if (
            $percentageRated > 0 &&
            $percentageRated < 100
        ) {
            return RatingStatus::Orange;
        }

        return RatingStatus::Grey;
    }

    /**
     * @param TestTakesController $testTakesController
     * @param TestTake $test_take
     * @return void
     */
    public function getTestParticipantsData(): void
    {
        $request = new Request([
            'with' => ['participantStatus'],
        ]);

        //get testTake from TestTakesController, also sets testParticipant 'abnormalities'
        $this->testTake = (new TestTakesController)->showFromWithin($this->testTake, $request, false);

        $this->testParticipantCount = $this->testTake->testParticipants->count();
        $this->testParticipantCountActive = $this->testTake->testParticipants->sum(fn ($tp) => $tp->active);

        //temp: sets all testParticipants on active todo remove
        //TODO REMOVE TEMP OVERWRITE
        $this->testTake->testParticipants->each(function($tp) {
            $tp->active = (
                AnswerRating::where('user_id', $tp->user_id)->where('test_take_id', $this->testTake->id)->exists()
            );
        });
    }

    protected function getNavigationData()
    {
        $questionsOrderList = collect($this->testTake->test->getQuestionOrderListWithDiscussionType());

        $this->questionCount = $questionsOrderList->count('id');
        $this->questionIndex = $questionsOrderList->get($this->testTake->discussing_question_id)['order'];

        if($this->testTake->discussion_type === self::DISCUSSION_TYPE_OPEN_ONLY) {
            $questionsOrderList = $questionsOrderList->filter(fn ($item) => $item['question_type'] === 'OPEN');
        }

        $this->firstQuestionId = $questionsOrderList->sortBy('order')->first()['id'];
        $this->lastQuestionId = $questionsOrderList->sortBy('order')->last()['id'];

        $this->questionIndexOpenOnly = $questionsOrderList->get($this->testTake->discussing_question_id)['order_open_only'];
        $this->questionCountOpenOnly = $questionsOrderList->count('id');
    }
}
