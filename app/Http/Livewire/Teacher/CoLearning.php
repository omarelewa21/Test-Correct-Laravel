<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use PHPUnit\Util\Test;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\CompletionQuestion;
use tcCore\Events\CoLearningNextQuestion;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;
use tcCore\Question;
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

    //answerRating properties
    public $activeAnswerRating = null;
    public $activeAnswerText = null;
    public $activeAnswerAnsweredStatus;

    //Question Navigation properties
    public $questionsOrderList;

    public int $firstQuestionId;
    public int $lastQuestionId;
    public int $questionCount;

    public int $questionCountOpenOnly;
    public int $questionIndex;
    public int $questionIndexOpenOnly;

    //CompletionQuestion specific properties
    public int $completionQuestionTagCount = 0;

    public function mount(TestTake $test_take)
    {
        //todo guard clause with test_take_status_id

        $this->testTake = $test_take;

        $this->openOnly = $this->testTake->discussion_type === self::DISCUSSION_TYPE_OPEN_ONLY;

        if ($test_take->discussing_question_id === null) {
            //gets a testTake, but misses TestParticipants Status data.
            // also creates AnswerRatings for students.
            //todo Remove unnecesairy TestTake Query? merge query actions? nextQuestion and participant activity data
            (new TestTakesController)->nextQuestion($test_take);
        }

        $this->getStaticNavigationData();
    }

    public function boot()
    {
        TestTake::$withAppends = false;
    }

    public function booted()
    {
        $this->getTestParticipantsData();

        $this->handleTestParticipantStatusses(); //todo move to polling method

        $this->getNavigationData();
    }

    public function render()
    {
        return view('livewire.teacher.co-learning')
            ->layout('layouts.co-learning-teacher');
    }

    public function nextDiscussionQuestion()
    {
        $this->removeChangesFromTestTakeModel();

        //do i have to refresh/fresh the TestTake before passing it to the controller?
        $this->testTake = (new TestTakesController)->nextQuestion($this->testTake, false);
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
        if (!$this->updateDiscussingQuestionIdOnTestTake()) {
            //todo what to do if updating fails?
            return false;
        }

        $this->testTake->discussingParentQuestions()->delete();

        //Pusher is not yet implemented at the student side of Co-Learning
//        foreach ($this->testTake->testParticipants as $testParticipant) {
//            CoLearningNextQuestion::dispatch($testParticipant->uuid);
//        }
    }

    public function showStudentAnswer($id)
    {
        $this->activeAnswerRating = AnswerRating::with('answer')->find($id);

        $this->setActiveAnswerAnsweredStatus();

        $this->setActiveAnswerText();
    }

    public function closeStudentAnswer()
    {
        $this->activeAnswerRating = null;

        $this->activeAnswer = null;
        $this->activeAnswerText = null;

        $this->activeAnswerAnsweredStatus = null;
    }

    /* end sidebar methods */

    public function getAtLastQuestionProperty()
    {
        return (int)$this->testTake->discussing_question_id === (int)$this->lastQuestionId;
    }

    public function getAtFirstQuestionProperty()
    {
        return (int)$this->testTake->discussing_question_id === (int)$this->firstQuestionId;
    }

    public function getPreviousQuestionIdProperty()
    {
        $currentQuestionOrder = $this->questionsOrderList[$this->testTake->discussing_question_id]['order'];

        return $this->questionsOrderList
            ->filter(fn($item) => $item['order'] < $currentQuestionOrder)
            ->sortByDesc('order')
            ->first()['id'];
    }

    public function getNextQuestionIdProperty()
    {
        $currentQuestionOrder = $this->questionsOrderList[$this->testTake->discussing_question_id]['order'];

        return $this->questionsOrderList
            ->filter(fn($item) => $item['order'] > $currentQuestionOrder)
            ->sortBy('order')
            ->first()['id'];
    }

    public function getAnswerModelHtmlProperty()
    {
        $question = $this->testTake->discussingQuestion;

        if ($question instanceof CompletionQuestion) {

            return $this->convertCompletionQuestionToHtml(
                $this->uniformCompletionQuestionAnswersDataObject('answer_model')
            );
//            return $this->convertCompletionQuestionToHtml($question->completionQuestionAnswers()->get());
        };

//        dd($question->completionQuestionAnswers);

        return $question->answer;
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

            $testParticipantPercentageRated = (!isset($testParticipant->answer_to_rate) || $testParticipant->answer_to_rate === 0)
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
            'with' => ['participantStatus', 'discussingQuestion'],
        ]);

        //get testTake from TestTakesController, also sets testParticipant 'abnormalities'
        $this->testTake = (new TestTakesController)->showFromWithin($this->testTake, $request, false);
//dd($this->testTake, $this->testTake->discussingQuestion()->first());
        $this->testParticipantCount = $this->testTake->testParticipants->count();
        $this->testParticipantCountActive = $this->testTake->testParticipants->sum(fn($tp) => $tp->active);

        //temp: sets all testParticipants on active todo remove
        //TODO REMOVE TEMP OVERWRITE
//        $this->testTake->testParticipants->each(function ($tp) {
//            $tp->active = (
//            AnswerRating::where('user_id', $tp->user_id)->where('test_take_id', $this->testTake->id)->exists()
//            );
//        });
    }

    protected function getNavigationData()
    {
        $this->questionIndex = $this->questionsOrderList->get($this->testTake->discussing_question_id)['order'];

        $this->questionIndexOpenOnly = $this->questionsOrderList->get($this->testTake->discussing_question_id)['order_open_only'];
    }

    protected function getStaticNavigationData()
    {
        $this->questionsOrderList = collect($this->testTake->test->getQuestionOrderListWithDiscussionType());

        $this->questionCount = $this->questionsOrderList->count('id');

        if ($this->testTake->discussion_type === self::DISCUSSION_TYPE_OPEN_ONLY) {
            $this->questionsOrderList = $this->questionsOrderList->filter(fn($item) => $item['question_type'] === 'OPEN');
        }
        $this->questionCountOpenOnly = $this->questionsOrderList->count('id');

        $this->firstQuestionId = $this->questionsOrderList->sortBy('order')->first()['id'];
        $this->lastQuestionId = $this->questionsOrderList->sortBy('order')->last()['id'];
    }

    public function setVideoTitle() {}

    private function convertCompletionQuestionToHtml(?Collection $answers = null)
    {
        $question = $this->testTake->discussingQuestion;

        $question->getQuestionHtml();

        $question_text = $question->converted_question_html;

        $this->completionQuestionTagCount = 0;

        $searchPattern = "/\[([0-9]+)\]/i";
        $replacementFunction = function ($matches) use ($question, $answers) {
            $this->completionQuestionTagCount++;
            $tag_id = $matches[1];
            $events = '';
            $rsSpan = '';
            return sprintf(
                '<span><input x-on:contextmenu="$event.preventDefault()" spellcheck="false" value="%s"   autocorrect="off" autocapitalize="none" class="form-input mb-2 truncate text-center overflow-ellipsis" type="text" id="%s" style="width: 120px" x-ref="%s" %s wire:key="%s"/>%s</span>',
                $answers?->where('tag', $tag_id)?->first()?->answer ?? '',
                'answer_' . $tag_id . '_' . $question->getKey(),
                'comp_answer_' . $tag_id,
                $events,
                'comp_answer_' . $tag_id,
                $rsSpan
            );
        };

        return preg_replace_callback($searchPattern, $replacementFunction, $question_text);
    }

    protected function updateDiscussingQuestionIdOnTestTake(): bool
    {
        $this->removeChangesFromTestTakeModel();

        return $this->testTake->update(['discussing_question_id' => $this->previousQuestionId]);
    }

    /**
     * @return void
     */
    public function removeChangesFromTestTakeModel(): void
    {
        $additionalDirtyAttributesWeDontWantToSave = collect(
            array_keys($this->testTake->getAttributes())
        )->diff(
            array_keys($this->testTake->getOriginal())
        );

        $additionalDirtyAttributesWeDontWantToSave->each(function ($attribute) {
            unset($this->testTake->$attribute);
        });
    }


    protected function uniformCompletionQuestionAnswersDataObject($source = null)
    {
        switch ($source) {
            case 'answer_model':
                // [ 0 => completionQuestionAnswer { tag => ; answer => ; }
                return $this->testTake->discussingQuestion->completionQuestionAnswers()->get()
                    ->map(function ($answer) {
                        $result = new \stdClass();
                        $result->tag = $answer->tag;
                        $result->answer = $answer->answer;
                        return $result;
                    });
            case 'student_answer':
            case 'answer_rating':
            case 'answers':
                // [ 0 => [ 0 => 'answer', 1 => 'answer']]
                return collect(json_decode(
                    json: $this->activeAnswerRating->answer->json,
                    associative: true
                ))->mapWithKeys(function ($answer, $tag) {
                    $result = new \stdClass();
                    $result->tag = $tag + 1; //database value is 0 based, tags are 1 based
                    $result->answer = $answer;
                    return [$tag => $result];
                });
            default:
                return null;
        }
    }

    /**
     * Set Active student answer Answered status:
     *  - answered
     *  - partly-answered
     *  - not-answered
     */
    public function setActiveAnswerAnsweredStatus()
    {
        if(!$this->activeAnswerRating->answer->isAnswered) {
            $this->activeAnswerAnsweredStatus = 'not-answered';
            return;
        }
        if($this->testTake->discussingQuestion instanceof CompletionQuestion) {
            $this->activeAnswerAnsweredStatus =  (collect($this->activeAnswerRating->answer)->count() === $this->completionQuestionTagCount)
                ? 'answered'
                : 'partly-answered';
            return;
        }
        $this->activeAnswerAnsweredStatus = 'answered';
    }

    public function setActiveAnswerText() : void
    {
        if ($this->testTake->discussingQuestion instanceof CompletionQuestion) {
            $this->activeAnswerText = $this->convertCompletionQuestionToHtml(
                $this->uniformCompletionQuestionAnswersDataObject('answers')
            );
            return;
        }

        $array = json_decode(
            json: $this->activeAnswerRating->answer->json,
            associative: true
        );

        //todo check if this covers all question types
        // - add drawing question answer?
        if (isset($array['value'])) {
            $this->activeAnswerText = $array['value'];
            return;
        }

        $this->activeAnswerText = '';
    }
}
