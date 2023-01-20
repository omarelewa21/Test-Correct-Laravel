<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Livewire\Component;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;
use tcCore\TestTake;

class CoLearning extends Component
{
    const DISCUSSION_TYPE_ALL = 'ALL';
    const DISCUSSION_TYPE_OPEN_ONLY = 'OPEN_ONLY';

    public int|TestTake $testTake;
    public bool $showStartOverlay = true;


    public $testParticipantStatusses;
    public float $testParticipantsFinishedWithRatingPercentage; //if 100.0, all possible answers have been rated //todo float or int?

    /*TODO
     * atFirstQuestionProperty
     * atLastQuestionProperty
     * handleFinishingCoLearning (clicking 'afronden')
    */
    public int $firstQuestionId;
    public int $lastQuestionId;
    public int $questionCount;

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;
        //TODO
        // First 'start_discussion'
        //   set $test_take['test_take_status_id'] = 7;
        //        $test_take['discussion_type'] = $type; (can be done in the CO-Learning choice screen)
        // .
        // .
        //   AND if discussion_question_id == null, 'nextDiscussionQuestion'
        //  next question => TestTakesControlle->nextQuestion()
        //  nextQuestion returns a TestTake, but it misses all testParticipant Status data...

        if ($test_take->discussing_question_id === null) {
            //gets a testTake, but misses TestParticipants Status data.
            // also creates AnswerRatings for students.
            (new TestTakesController)->nextQuestion($test_take);
        }

    }

    public function render()
    {
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
         * Get the next Question, make all students go to the next question
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
        /*TODO A ‘Finish’ (Afronden) button; disabled until last question.
         * Clicking it will bring the test and teacher screen to ‘Scoring’ (Nakijken en Normeren)
         */
        return $this->testTake->discussing_question_id === $this->lastQuestionId;
    }

    public function getAtFirstQuestionProperty()
    {
        /*TODO Previous question 'Text Button M - icon left':
         * Start of CO-Learning, while at first question; disabled.
         */
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

            $testParticipantPercentageRated = ($testParticipant->answer_rated / $testParticipant->answer_to_rate) * 100;

            $this->testParticipantStatusses[$testParticipant->uuid] = [
                'ratingStatus' => $this->getRatingStatusForTestParticipant($testParticipantPercentageRated)
            ];
        });


        //todo check if testParticipant is active?
        $abnormalitiesTotal = $this->testTake->testParticipants->sum(fn($tp) => isset($tp->abnormalities) ? $tp->abnormalities : 0);
        $abnormalitiesCount = $this->testTake->testParticipants->sum(fn($tp) => isset($tp->abnormalities));
        $abnormalitiesAverage = ($abnormalitiesCount === 0) ? 0 : $abnormalitiesTotal / $abnormalitiesCount;

//temp
//        $abnormalitiesTotal = 10;
//        $abnormalitiesCount = 5;
//        $abnormalitiesAverage = ($abnormalitiesCount === 0) ? 0 : $abnormalitiesTotal / $abnormalitiesCount; //average = 2 abnormalities

        $this->testTake->testParticipants->each(function ($testParticipant) use (&$abnormalitiesAverage) {
            if (!$testParticipant->active) {
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


        //temp: sets all testParticipants on active todo remove
        $this->testTake->testParticipants->each(fn($tp) => $tp->active = true);

        //temp overrides
//        $this->testTake->testParticipants->each(fn($tp) => $tp->answer_to_rate = 2);
//        $this->testTake->testParticipants->each(fn($tp) => $tp->answer_rated = rand(0,2));
//
//        $this->testTake->testParticipants->each(fn($tp) => $tp->abnormalities = rand(0,5));
////        $this->testTake->testParticipants->each(fn($tp) => $tp->answer_rated = 2);
    }

    protected function getNavigationData()
    {
        //get question index filtered on discussion_type
        //get question index with all questions discussion_types
        //get question count


        //get previous available
        //get next     available

        $questionsOrderList = collect($this->testTake->test->getQuestionOrderListWithDiscussionType());

        $this->questionCount = $questionsOrderList->count('id');

        if($this->testTake->discussion_type === self::DISCUSSION_TYPE_OPEN_ONLY) {
            $questionsOrderList = $questionsOrderList->filter(fn ($item) => $item['question_type'] === 'OPEN');
        }

        $this->firstQuestionId = $questionsOrderList->sortBy('order')->first()['id'];
        $this->lastQuestionId = $questionsOrderList->sortBy('order')->last()['id'];

        $this->questionIndex = $questionsOrderList->get($this->testTake->discussing_question_id)['order'];
        $this->questionIndexOpenOnly = $questionsOrderList->get($this->testTake->discussing_question_id)['order_open_only'];

        $this->questionCountOpenOnly = $questionsOrderList->count('id');

        $questionsCollection = TestTakeLaravelController::getData(null, $this->testTake);
        dd(
            $questionsOrderList,
            $this->firstQuestionId,
            $this->lastQuestionId,
            $this->questionIndex,
            $this->questionIndexOpenOnly,
            $this->questionCount,
            $this->questionCountOpenOnly,
        );

        $currentQuestionId = $this->testTake->discussingQuestion->getKey();

        $this->questionIndexNumber = $questionsOrder[$this->testTake->discussingQuestion->getKey()];



        $this->numberOfQuestions = $questionsCollection->reduce(function ($carry, $question) use ($currentQuestionId) {
            if ($this->discussOpenQuestionsOnly && $question->canCheckAnswer()) { //question canCheckAnswer === 'Closed question'
                return $carry;
            }
            $carry++;
            if ($question->id == $currentQuestionId) {
                $this->questionFollowUpNumber = $carry;
            }
            return $carry;
        }, 0);

        dd($this->questionIndexNumber, $this->questionFollowUpNumber);
    }
}
