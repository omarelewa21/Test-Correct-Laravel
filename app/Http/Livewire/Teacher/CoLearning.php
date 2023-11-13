<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\AnswerRating;
use tcCore\CompletionQuestion;
use tcCore\DiscussingParentQuestion;
use tcCore\DrawingQuestion;
use tcCore\Events\TestTakeCoLearningPresenceEvent;
use tcCore\Events\TestTakeForceTakenAway;
use tcCore\Events\TestTakeLeave;
use tcCore\Events\TestTakeStop;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\CoLearningHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Middleware\AfterResponse;
use tcCore\MatchingQuestion;
use tcCore\MultipleChoiceQuestion;
use tcCore\Question;
use tcCore\TestTake;
use tcCore\TestTakeQuestion;
use tcCore\TestTakeStatus;
use tcCore\Traits\CanSetUpCoLearning;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

class CoLearning extends TCComponent implements CollapsableHeader
{
    use CanSetUpCoLearning;

    //start screen properties
    public bool $coLearningHasBeenStarted = true;
    public bool $headerCollapsed = false;
    public bool $coLearningRestart = false;

    //TestTake properties
    private $testTake;
    public $testTakeUuid;
    private $discussingQuestion;

    public $group;

    //TestParticipant properties
    public $testParticipants;
    public $testParticipantsPresence = [];
    public $testParticipantStatusses;

    public float $testParticipantsFinishedWithRatingPercentage; //if 100.0, all possible answers have been rated
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

    public int $questionCountFiltered; // filtered question count (only discussed questions)
    public int $questionIndex; // filtered question index (only discussed questions)
    public int $questionIndexAsInTest; // unfiltered question index (as in test)
    public int $discussedQuestionsCount;

    protected $queryString = [
        'coLearningHasBeenStarted' => ['except' => true, 'as' => 'started']
    ];

    protected function getListeners()
    {
        return [
            TestTakeCoLearningPresenceEvent::channelHereSignature($this->testTake->uuid)    => 'initializingPresenceChannel',
            TestTakeCoLearningPresenceEvent::channelJoiningSignature($this->testTake->uuid) => 'joiningPresenceChannel',
            TestTakeCoLearningPresenceEvent::channelLeavingSignature($this->testTake->uuid) => 'leavingPresenceChannel',
        ];
    }

    public function initializingPresenceChannel($data)
    {
        $this->testParticipantsPresence = collect($data)->filter(fn($testParticipant) => $testParticipant['student'])
            ->mapWithKeys(fn($testParticipant) => [$testParticipant['testparticipant_uuid'] => $testParticipant]);
    }

    public function toggleStudentSpellcheck($val)
    {
        $this->testTake->enable_spellcheck_colearning = $val;
        $this->testTake->save();
    }

    public function toggleStudentEnableComments(bool $boolean)
    {
        $this->testTake->enable_comments_colearning = $boolean;
        $this->testTake->save();
    }


    public function toggleStudentEnableQuestionText(bool $boolean)
    {
        //if question text is disabled, also disable answer model and self pacing navigation
        if(!$boolean) {
            $this->testTake->enable_answer_model_colearning = $boolean;
            $this->testTake->enable_student_navigation_colearning = $boolean;
        }
        $this->testTake->enable_question_text_colearning = $boolean;
        $this->testTake->save();
    }

    public function toggleStudentEnableAnswerModel(bool $boolean)
    {
        //if answer model is enabled, also disable self pacing navigation
        if(!$boolean) {
            $this->testTake->enable_student_navigation_colearning = $boolean;
        }
        $this->testTake->enable_answer_model_colearning = $boolean;
        $this->testTake->save();
    }

    public function toggleStudentEnableNavigation(bool $boolean)
    {
        $this->testTake->enable_student_navigation_colearning = $boolean;
        $this->testTake->save();
    }

    public function toggleStudentAllowBrowserAccess(bool $boolean)
    {
        $this->testTake->allow_inbrowser_colearning = $boolean;
        $this->testTake->save();
    }

    public function joiningPresenceChannel($data)
    {
        $this->testParticipantsPresence = collect($this->testParticipantsPresence)->merge([$data['testparticipant_uuid'] => $data]);
    }

    public function leavingPresenceChannel($data)
    {
        $this->testParticipantsPresence = collect($this->testParticipantsPresence)->except($data['testparticipant_uuid']);
    }


    public function mount(TestTake $test_take)
    {
        Gate::authorize('isAllowedToViewTestTake',[$test_take, false, false ]);

        $this->testTakeUuid = $test_take->uuid;

        $this->setTestTake();

        $this->redirectIfNotAllowed();

        $this->getStaticNavigationData();

        if ($this->testTakeHasNotYetBeenStartedBefore() || !$this->coLearningHasBeenStarted) {
            $this->getSetUpData(true);
        }

        if ($this->testTakeIsBeingRestarted()) {
            $this->coLearningRestart = true;
            return;
        }
        if ($this->coLearningHasBeenStarted === false) {
            return;
        }

        if ($this->testTakeHasNotYetBeenStartedBefore()) {
            $this->coLearningHasBeenStarted = false;
            $this->headerCollapsed = false;
            return;
        }

        $this->getTestParticipantsData();

        $this->headerCollapsed = true;
    }

    public function boot()
    {
        TestTake::$withAppends = false;
    }

    public function hydrate()
    {
        $this->setTestTake();
        if ($this->coLearningHasBeenStarted === false) {
            return;
        }
        $this->getTestParticipantsData();
    }

    public function render()
    {
        return view('livewire.teacher.co-learning')
            ->layout('layouts.co-learning-teacher');
    }

    public function startCoLearningSession() : bool|Redirector
    {
        $testTakeUpdateData = [];
        if ($this->testTakeStatusNeedsToBeUpdated()) {
            $testTakeUpdateData['test_take_status_id'] = TestTakeStatus::STATUS_DISCUSSING;
        }
        if (!empty($testTakeUpdateData)) {
            $this->testTake->update($testTakeUpdateData);
            $this->testTake->refresh();
        }

        if ($this->testTake->discussing_question_id === null) {
            //gets a testTake, also creates AnswerRatings for students.
            //todo improve nextQuestion performance? move generating all answerRatings to start of CoLearning?
            CoLearningHelper::nextQuestion($this->testTake);
            $this->discussingQuestion = $this->testTake->discussingQuestion()->first();
        }

        if (!settings()->allowNewCoLearningTeacher(auth()->user())) {
            return CakeRedirectHelper::redirectToCake('test_takes.discussion', $this->testTake->uuid);
        }

        //finally set bool to true
        $this->coLearningHasBeenStarted = true;
        $this->headerCollapsed = true;
        $this->getStaticNavigationData();
        $this->refreshComponentData();

        return $this->coLearningHasBeenStarted;
    }

    public function nextDiscussionQuestion()
    {
        $this->removeChangesFromTestTakeModel();
        $this->testTake = CoLearningHelper::nextQuestion($this->testTake);
    }

    /* start header methods */
    public function redirectBack()
    {
        $this->testTake->update(['test_take_status_id' => 6]);
        AfterResponse::$performAction[] = fn() => TestTakeLeave::dispatch($this->testTake->uuid);

        return TestTake::redirectToDetail(
            testTakeUuid: $this->testTake->uuid,
            returnRoute: Str::replaceFirst(config('app.base_url'), '', Livewire::originalUrl()),
        );
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
        $this->nextDiscussionQuestion();
        $this->getNavigationData();

        $this->refreshComponentData();
    }

    public function goToPreviousQuestion()
    {
        if (!$this->updateDiscussingQuestionIdOnTestTake()) {
            return false;
        }

        $this->getNavigationData();

        $this->refreshComponentData();
    }

    public function showStudentAnswer($id): bool
    {
        if ((int)$id === $this->activeAnswerRating?->id) {
            $this->resetActiveAnswer();
            return false;
        }

        if ($id === null || $id === '') {
            return false;
        }

        $this->activeAnswerRating = AnswerRating::with('answer')->find($id);

        $this->setActiveAnswerAnsweredStatus();

        $this->setActiveAnswerText();

        return true;
    }

    public function resetActiveAnswer()
    {
        $this->activeAnswerRating = null;

        $this->activeAnswerText = null;

        $this->activeAnswerAnsweredStatus = null;
    }

    /* end sidebar methods */

    public function getAtLastQuestionProperty()
    {
        if (!$this->coLearningHasBeenStarted) {
            return false;
        }
        return (int)$this->testTake->discussing_question_id === (int)$this->lastQuestionId;
    }

    public function getAtFirstQuestionProperty()
    {
        if (!$this->coLearningHasBeenStarted) {
            return false;
        }
        return (int)$this->testTake->discussing_question_id === (int)$this->firstQuestionId;
    }

    public function getPreviousQuestionIdProperty()
    {
        $this->resetActiveAnswer();

        $discussingQuestionId = $this->testTake?->fresh()->discussing_question_id;

        if ($discussingQuestionId === null) {
            return null;
        }

        $currentQuestionOrder = $this->questionsOrderList[$discussingQuestionId]['order'];

        return $this->questionsOrderList
            ->filter(fn($item) => $item['order'] < $currentQuestionOrder)
            ->sortByDesc('order')
            ->first()['id'];
    }

    public function getNextQuestionIdProperty()
    {
        $this->resetActiveAnswer();

        $currentQuestionOrder = $this->questionsOrderList[$this->testTake->discussing_question_id]['order'];

        return $this->questionsOrderList
            ->filter(fn($item) => $item['order'] > $currentQuestionOrder)
            ->sortBy('order')
            ->first()['id'];
    }

    public function getAnswerModelHtmlProperty()
    {
        $question = $this->discussingQuestion;

        if ($question instanceof CompletionQuestion) {
            return $this->convertCompletionQuestionToHtml(
                $this->uniformCompletionQuestionAnswersDataObject('answer_model')
            );
        };

        return $question->answer;
    }

    public function getDrawingAnswerModelUrlProperty()
    {
        return route('teacher.drawing-question-answer-model', $this->discussingQuestion->uuid);
    }

    private function handleFinishingCoLearning()
    {
        $this->testTake->update([
            'test_take_status_id' => 8,
            'skipped_discussion'  => false,
        ]);
        AfterResponse::$performAction[] = fn() => TestTakeStop::dispatch($this->testTake->uuid);
    }

    private function handleTestParticipantStatusses(): void
    {
        //reset values
        $this->testParticipantStatusses = collect();

        $testParticipantsCount = $this->testParticipants->sum(fn($tp) => $this->testParticipantIsActive($tp) === true);

        $testParticipantsFinishedWithRatingCount = $this->testParticipants->sum(
            fn($tp) => ($tp->answer_to_rate === $tp->answer_rated) && $this->testParticipantIsActive($tp)
        );

        $this->testParticipantsFinishedWithRatingPercentage = $testParticipantsCount > 0
            ? $testParticipantsFinishedWithRatingCount / $testParticipantsCount * 100
            : 0;
        $this->testParticipants->each(function ($testParticipant) {

            $this->testParticipantStatusses[$testParticipant->uuid] = [
                'ratingStatus' => RatingStatus::get(
                    $testParticipant->answer_to_rate,
                    $testParticipant->answer_rated,
                    $this->testParticipantsFinishedWithRatingPercentage
                )
            ];
        });

        $abnormalitiesTotal = $this->testParticipants->sum(
            fn($tp) => ($this->testParticipantIsActive($tp) && isset($tp->abnormalities)) ? $tp->abnormalities : 0
        );
        $abnormalitiesCount = $this->testParticipants->sum(fn($tp) => $this->testParticipantIsActive($tp) && isset($tp->abnormalities));
        $abnormalitiesAverage = ($abnormalitiesCount === 0) ? 0 : $abnormalitiesTotal / $abnormalitiesCount;

        $this->testParticipants->each(function ($testParticipant) use (&$abnormalitiesAverage) {

            $answersRatedByTestParticipant = DB::table('answer_ratings')
                ->where('test_take_id', $this->testTake->getKey())
                ->where('user_id', $testParticipant->user_id)->get()
                ->where('rating', '<>', null)
                ->where('deleted_at', '=', null)
                ->count();

            $this->testParticipantStatusses = $this->testParticipantStatusses->mergeRecursive([
                $testParticipant->uuid => [
                    'abnormalitiesStatus' => AbnormalitiesStatus::get(
                        testParticipantAbnormalities: $testParticipant->abnormalities,
                        averageAbnormalitiesAmount: $abnormalitiesAverage,
                        enoughDataAvailable: $answersRatedByTestParticipant >= 4,
                    )
                ]
            ]);
        });

        $this->testParticipants = $this->testParticipants
            ->sortBy(fn($testParticipant) => $testParticipant->user->nameFull)
            ->sortBy(function ($testParticipant) {
                if (!isset($this->testParticipantStatusses[$testParticipant->uuid])) {
                    return 0;
                }

                $order = 0;
                $order += $this->testParticipantStatusses[$testParticipant->uuid]['ratingStatus']?->getSortWeight();
                $order += $this->testParticipantStatusses[$testParticipant->uuid]['abnormalitiesStatus']?->getSortWeight();

                return $order;
            });
    }

    private function testParticipantIsActive($testParticipant): bool
    {
        return $testParticipant->active || isset($this->testParticipantsPresence[$testParticipant->uuid]);
    }

    private function getAbnormalitiesStatusForTestParticipant($averageDeltaPercentage): AbnormalitiesStatus
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

    private function getRatingStatusForTestParticipant($percentageRated): RatingStatus
    {
        if (intval($percentageRated) === 100) {
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
        $this->testParticipants = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities(
            $this->testTake->getKey(),
            $this->discussingQuestion?->getKey(),
        );

        $this->testParticipants
            ->load([
                'discussingAnswerRating:id,answer_id',
                'discussingAnswerRating.answer:id,question_id',
                'user',
            ])
            ->whereNotNull('discussing_answer_rating_id')
            ->each(function ($participant) {
                $participant->syncedWithCurrentQuestion = $participant->discussingAnswerRating?->answer->question_id === $this->discussingQuestion?->id;
            });

        $this->testParticipantCount = $this->testParticipants->count();
        $this->testParticipantCountActive = $this->testParticipants->sum(fn($tp) => $this->testParticipantIsActive($tp));

        $this->handleTestParticipantStatusses();
    }


    protected function getNavigationData()
    {
        $this->questionIndex = 0;

        if (!isset($this->testTake->discussing_question_id)) {
            return false;
        }

        if ($this->questionsOrderList->get($this->testTake->discussing_question_id)) {
            $this->questionIndex = $this->questionsOrderList->get($this->testTake->discussing_question_id)['order'];
            $this->questionIndexAsInTest = $this->questionsOrderList->get($this->testTake->discussing_question_id)['order_in_test'];
        }
    }

    protected function getStaticNavigationData()
    {
        $this->questionsOrderList = $this->getQuestionList();

        $this->questionCount = $this->questionsOrderList->count('id');
        $this->discussedQuestionsCount = $this->questionsOrderList
            ->filter(fn($question) => isset($question['discussed']) && (bool)$question['discussed'])
            ->count();

        //filter questions that have 'discuss in class' on false
        $this->questionsOrderList = $this->questionsOrderList->filter(fn($item) => (bool)$item['discuss']);
        $this->questionCountFiltered = $this->questionsOrderList->count('id');

        $this->firstQuestionId = $this->questionsOrderList->sortBy('order')->first()['id'];
        $this->lastQuestionId = $this->questionsOrderList->sortBy('order')->last()['id'];

        $this->getNavigationData();
    }

    private function convertCompletionQuestionToHtml(?Collection $answers = null)
    {
        return Blade::renderComponent(
            new CompletionQuestionConvertedHtml(
                $this->testTake->discussingQuestion,
                'teacher-colearning',
                $answers
            )
        );
    }

    protected function updateDiscussingQuestionIdOnTestTake(): bool
    {
        $this->removeChangesFromTestTakeModel();

        if ($this->previousQuestionId !== null) {
            $this->testTake->update(['discussing_question_id' => $this->previousQuestionId]);
            $this->testTake->refresh();
            return true;
        }

        return false;
    }

    protected function removeChangesFromTestTakeModel(): void
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
                return $this->discussingQuestion->completionQuestionAnswers()->get()
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
                return collect(
                    json_decode(
                        json: $this->activeAnswerRating->answer->json,
                        associative: true
                    )
                )->mapWithKeys(function ($answer, $tag) {
                    $result = new \stdClass();
                    $result->tag = intval($tag) + 1; //database value is 0 based, tags are 1 based
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
    protected function setActiveAnswerAnsweredStatus()
    {
        $this->activeAnswerAnsweredStatus = $this->activeAnswerRating->answer->answeredStatus;
        return;
    }

    protected function setActiveAnswerText(): void
    {
        if ($this->discussingQuestion instanceof CompletionQuestion) {
            $this->activeAnswerText = $this->convertCompletionQuestionToHtml(
                $this->uniformCompletionQuestionAnswersDataObject('answers')
            );
            return;
        }
        if ($this->discussingQuestion instanceof DrawingQuestion) {
            $this->activeAnswerText = route('teacher.drawing-question-answer', $this->activeAnswerRating->answer->uuid);
            return;
        }

        $array = json_decode(
            json: $this->activeAnswerRating->answer->json,
            associative: true
        );

        $this->activeAnswerText = $array['value'] ?? '';
    }

    /**
     * @param mixed $discussingQuestionId
     * @return mixed|null
     */
    protected function getGroupQuestionIdForSubQuestion(mixed $questionId): null|int
    {
        return DB::query()
            ->select('group_question_questions.group_question_id')
            ->from('test_takes')
            ->join('tests', 'tests.id', '=', 'test_takes.test_id')
            ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
            ->join(
                'group_question_questions',
                'group_question_questions.group_question_id',
                '=',
                'test_questions.question_id'
            )
            ->where('test_takes.id', '=', $this->testTake->getKey())
            ->where('group_question_questions.question_id', '=', $questionId)->first()?->group_question_id;
    }

    private function redirectIfNotAllowed(): Redirector|null
    {
        if (!in_array(
                $this->testTake->test_take_status_id,
                [
                    TestTakeStatus::STATUS_TAKEN,
                    TestTakeStatus::STATUS_DISCUSSING,
                    TestTakeStatus::STATUS_DISCUSSED,
                    TestTakeStatus::STATUS_RATED,
                ]
            )) {
            return redirect()->route('teacher.test-takes', ['stage' => 'taken']);
        }
        return null;
    }

    private function testTakeIsBeingRestarted(): bool
    {
        return $this->coLearningHasBeenStarted === false
            && $this->testTake->discussing_question_id !== null
            && $this->testTake->test_take_status_id >= TestTakeStatus::STATUS_TAKEN;
    }

    private function testTakeHasNotYetBeenStartedBefore(): bool
    {
        return $this->testTake->discussing_question_id === null
            || $this->testTake->test_take_status_id === TestTakeStatus::STATUS_TAKEN;
    }

    private function testTakeStatusNeedsToBeUpdated(): bool
    {
        return $this->testTake->test_take_status_id !== TestTakeStatus::STATUS_DISCUSSING;
    }

    private function deleteStudentAnswerRatings()
    {
        AnswerRating::where('test_take_id', '=', $this->testTake->getKey())
            ->where('type', '=', AnswerRating::TYPE_STUDENT)
            ->delete();
    }

    public function handleHeaderCollapse($args): bool
    {
        return $this->startCoLearningSession();
    }

    private function getQuestionList()
    {
        $testTakeQuestions = $this->getTestTakeQuestions()
            ?->keyBy('question_id');

        $orderList = collect($this->testTake->test->getQuestionOrderListWithDiscussionType());

        if($testTakeQuestions->isEmpty()) {
            return $orderList;
            $order = 1;
            return $orderList->map(function ($question) use (&$order) {
                $question['order'] = $order++;
                $question['discussed'] = false;
                return $question;
            });;
        }

        //filters questions that are not checked at start screen
        // recalculates order of questionList
        $order = 1;
        return $orderList->filter(fn($question) => $testTakeQuestions->has($question['id']))
        ->map(function ($question) use (&$order, $testTakeQuestions) {
            $question['order'] = $order++;
            $question['discussed'] = (bool) $testTakeQuestions->get($question['id'])->discussed;
            return $question;
        });
    }

    private function setTestTake()
    {
        $this->testTake = cache()->remember('co-learning-teacher-' . $this->testTakeUuid, now()->addDays(3), function () {
            return TestTake::whereUuid($this->testTakeUuid)
                ->with([
                    'test',
                    'test.testQuestions',
                    'test.testQuestions.question',
                    'discussingQuestion',
                    'discussingParentQuestions'              => fn($query) => $query->orderBy('level'),
                    'testParticipants',
                    'testParticipants.answers:id,test_participant_id,uuid,done,question_id',
                    'testParticipants.answers.answerRatings' => fn($query) => $query->where('type', 'STUDENT'),
                    'testParticipants.answers.answerParentQuestions',
                ])->first();
        });

        $this->testTake = $this->testTake->refresh();
        $this->testTake->testParticipants->load(['answers.answerRatings' => fn($query) => $query->where('type', 'STUDENT')]);
        $this->discussingQuestion = $this->testTake->discussingQuestion()->first();
        if ($this->discussingQuestion) {
            $this->group = $this->discussingQuestion->getGroupQuestion($this->testTake);
        }
    }

    private function refreshComponentData(): void
    {
        $this->resetActiveAnswer();
        $this->discussingQuestion = $this->testTake->discussingQuestion()->first();
        $this->setTestTakeQuestionDiscussed();

        $this->group = $this->discussingQuestion->getGroupQuestion($this->testTake);
        $this->getTestParticipantsData();
        $this->testParticipants->map(fn($participant) => $participant->syncedWithCurrentQuestion = false);
    }

    private function setTestTakeQuestionDiscussed()
    {
        $discussingTestTakeQuestion = $this->testTake->testTakeQuestions->where('question_id', $this->discussingQuestion->id)->first();

        if(!$discussingTestTakeQuestion->discussed) {
            $discussingTestTakeQuestion->update(['discussed' => true]);
        }
    }

    private function getDisplayableQuestionText()
    {
        if ($this->discussingQuestion->isType('Completion')) {
            return Blade::renderComponent(new CompletionQuestionConvertedHtml($this->discussingQuestion, 'assessment'));
        }
        return $this->discussingQuestion->converted_question_html;
    }

}
