<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Livewire\Component;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\ScoringStatus;
use tcCore\TestTake;

class CoLearning extends Component
{
    public int|TestTake $testTake;
    public $testTakeParticipantStatusses;
    public bool $allCurrentAnswerRatingsDiscussed;

    /*TODO
     * atFirstQuestionProperty
     * atLastQuestionProperty
     * handleFinishingCoLearning (clicking 'afronden')
    */

    public function mount(TestTake $test_take)
    {
        $request = new Request([
            'with' => ['participantStatus'],
        ]);

        //get testTake from TestTakesController, also sets testParticipant 'abnormalities'
        $this->testTake = (new TestTakesController)->showFromWithin($test_take, $request, false);


        $this->handleTestParticipantStatusses();
    }

    public function render()
    {
        return view('livewire.teacher.co-learning')
            ->layout('layouts.co-learning-teacher');
    }

    /* start header methods */
    public function redirectBack()
    {
        return redirect()->route('teacher.test-takes', ['stage' => 'taken']);
    }

    public function getAtLastQuestionProperty()
    {
        /*TODO A ‘Finish’ (Afronden) button; disabled until last question.
         * Clicking it will bring the test and teacher screen to ‘Scoring’ (Nakijken en Normeren)
         */
        return false;
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
    }

    public function goToPreviousQuestion()
    {
        /*TODO
         * NEW FUNCTIONALITY! DOESNT WORK WITH OLD CO_LEARNING CONTROLLERS!
         * Get the previous Question, make all students go to the previous question
         */
    }

    public function getAtFirstQuestionProperty()
    {
        /*TODO Previous question 'Text Button M - icon left':
         * Start of CO-Learning, while at first question; disabled.
         */
        return true;
    }

    public function showStudentAnswer($uuidOrId)
    {
        //TODO show the answer, the student is now rating, on the teacher screen.
    }

    /* end sidebar methods */

    private function handleFinishingCoLearning()
    {
        //TODO change test_take_status_id (to Discussed?) before redirecting to 'nakijken en normeren'
    }

    private function handleTestParticipantStatusses()
    {

        $this->testTakeParticipantStatusses = collect();
        $this->allCurrentAnswerRatingsDiscussed = true;

        $totalAbnormalities = 0;
        $countAbnormalities = 0;
        $averageOfAbnormalities = 0;


        $this->testTake->testParticipants->each(function ($testParticipant) use (&$totalAbnormalities, &$countAbnormalities) {
            $percentageScored = ($testParticipant->answer_rated / $testParticipant->answer_to_rate) * 100;

            if ($testParticipant->active || true) { //todo remove true
//            if (isset($testParticipant->answer_rated)) {
                if ($testParticipant->answer_rated == $testParticipant->answer_to_rate) {
                    //first check 100% green,
                    $this->testTakeParticipantStatusses[$testParticipant->uuid] = ['scoringStatus' => ScoringStatus::Green];
                } elseif ($testParticipant->answer_rated == 'IMPLEMENT RED LOGIC') {
                    //then check red

                    // todo implement calculation for red


                    $this->testTakeParticipantStatusses[$testParticipant->uuid] = ['scoringStatus' => ScoringStatus::Red];
                    $this->allCurrentAnswerRatingsDiscussed = false;
                } elseif ($percentageScored > 0 && $percentageScored < 100) {
                    //then less than 100% but not 0% === orange
                    $this->testTakeParticipantStatusses[$testParticipant->uuid] = ['scoringStatus' => ScoringStatus::Orange];
                    $this->allCurrentAnswerRatingsDiscussed = false;
                } else {
                    //default: (if not other colors, it is 0% but not red, for example at the start)
                    $this->testTakeParticipantStatusses[$testParticipant->uuid] = ['scoringStatus' => ScoringStatus::Grey];
                    $this->allCurrentAnswerRatingsDiscussed = false;
                }
//            }
            } else {
                // testParticipant is not active...
                // todo set color Grey anyway?
            }


            if(isset($testParticipant->abnormalities)) {
                $totalAbnormalities += $testParticipant->abnormalities;
                $countAbnormalities++;
            }


            AbnormalitiesStatus::Happy;
            AbnormalitiesStatus::Neutral;
            AbnormalitiesStatus::Sad;
            AbnormalitiesStatus::Default;




            return $testParticipant;
        });

        //abnormalities
        if($countAbnormalities > 0) {
            $averageOfAbnormalities = $totalAbnormalities / $countAbnormalities;
        }
        dd($averageOfAbnormalities);


        $this->testTake->testParticipants->each(function ($testParticipant) use (&$totalAbnormalities, &$countAbnormalities, &$averageOfAbnormalities) {

            //todo determine smiley with the average.
            

        });

    }
}
