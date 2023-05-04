<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use tcCore\AnswerRating;
use tcCore\CompletionQuestion;
use tcCore\DiscussingParentQuestion;
use tcCore\DrawingQuestion;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\CoLearningHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class CoLearning extends Component implements CollapsableHeader
{
    const DISCUSSION_TYPE_ALL = 'ALL';
    const DISCUSSION_TYPE_OPEN_ONLY = 'OPEN_ONLY';

    //start screen properties
    public ?bool $coLearningHasBeenStarted = true;
    public bool $headerCollapsed = false;
    public bool $coLearningRestart = false;

    //TestTake properties
    public int|TestTake $testTake;
    public $testParticipants;

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

    public int $questionCountFiltered;
    public int $questionIndex;
    public int $questionIndexOpenOnly;

    //CompletionQuestion specific properties
    public int $completionQuestionTagCount = 0;
    public ?Collection $activeDrawingAnswerDimensions;

    protected $queryString = [
        'coLearningHasBeenStarted' => ['except' => true, 'as' => 'started']
    ];

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;
        $this->redirectIfNotAllowed();

        $this->getStaticNavigationData();

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

        $this->headerCollapsed = true;
    }

    public function boot()
    {
        TestTake::$withAppends = false;
    }

    public function booted()
    {
        //
    }

    public function render()
    {
        $this->getTestParticipantsData();

        return view('livewire.teacher.co-learning')
            ->layout('layouts.co-learning-teacher');
    }

    public function startCoLearningSession($discussionType)
    {
        $resetProgress = $discussionType != $this->testTake->discussion_type;

        if (!in_array($discussionType, ['OPEN_ONLY', 'ALL'])) {
            throw new \Exception('Wrong discussion type');
        }

        $testTakeUpdateData = [];
        if ($this->testTakeStatusNeedsToBeUpdated()) {
            $testTakeUpdateData['test_take_status_id'] = TestTakeStatus::STATUS_DISCUSSING;
        }
        if ($this->discussionTypeNeedsToBeUpdated($discussionType)) {
            $testTakeUpdateData['discussion_type'] = $discussionType;
        }
        if ($resetProgress) {
            $testTakeUpdateData['discussing_question_id'] = null;
            $testTakeUpdateData['is_discussed'] = 0;
            $this->deleteStudentAnswerRatings();
        }
        if (!empty($testTakeUpdateData)) {
            $this->testTake->update($testTakeUpdateData);
            $this->testTake->refresh();
        }

        if ($this->testTake->discussing_question_id === null) {

            //gets a testTake, also creates AnswerRatings for students.
            //todo improve nextQuestion performance? move generating all answerRatings to start of CoLearning?
            CoLearningHelper::nextQuestion($this->testTake);
        }

        if ($discussionType === 'ALL') {
            return CakeRedirectHelper::redirectToCake('test_takes.discussion', $this->testTake->uuid);
        }

        //finally set bool to true
        $this->coLearningHasBeenStarted = true;
        $this->headerCollapsed = true;
        $this->getStaticNavigationData();
    }

    public function nextDiscussionQuestion()
    {
        $this->removeChangesFromTestTakeModel();

        $this->testTake = (new TestTakesController)->nextQuestion($this->testTake, false);
    }

    /* start header methods */
    public function redirectBack()
    {
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
    }

    public function goToPreviousQuestion()
    {
        if (!$this->updateDiscussingQuestionIdOnTestTake()) {
            return false;
        }

        $discussingQuestion = $this->testTake->discussingQuestion()->first();
        $this->testTake->getKey();

        $this->testTake->discussingParentQuestions()->delete();

        if ($discussingQuestion?->is_subquestion) {
            $discussingQuestionId = $discussingQuestion->getKey();

            $groupQuestionId = $this->getGroupQuestionIdForSubQuestion($discussingQuestionId);

            $discussingParentQuestion = new DiscussingParentQuestion();
            $discussingParentQuestion->group_question_id = $groupQuestionId;
            $discussingParentQuestion->level = 1;

            $this->testTake->discussingParentQuestions()->save($discussingParentQuestion);
        }
        $this->getNavigationData();


        //Pusher is not yet implemented at the student side of Co-Learning
//        foreach ($this->testParticipants as $testParticipant) {
//            CoLearningNextQuestion::dispatch($testParticipant->uuid);
//        }
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

        $this->activeDrawingAnswerDimensions = null;
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

        if($discussingQuestionId === null) {
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
        $question = $this->testTake->discussingQuestion;

        if ($question instanceof CompletionQuestion) {

            return $this->convertCompletionQuestionToHtml(
                $this->uniformCompletionQuestionAnswersDataObject('answer_model')
            );
        };

        return $question->answer;
    }

    public function getDrawingAnswerModelUrlProperty()
    {
        return route('teacher.drawing-question-answer-model', $this->testTake->discussingQuestion->uuid);
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

        $testParticipantsCount = $this->testParticipants->sum(fn($tp) => $tp->active === true);
        if ($testParticipantsCount === 0) {
            return;
        }

        $testParticipantsFinishedWithRatingCount = $this->testParticipants->sum(fn($tp) => ($tp->answer_to_rate === $tp->answer_rated) && $tp->active);

        $this->testParticipantsFinishedWithRatingPercentage = $testParticipantsCount > 0
            ? $testParticipantsFinishedWithRatingCount / $testParticipantsCount * 100
            : 0;

        $this->testParticipants->each(function ($testParticipant) {
            if (!$testParticipant->active) {
                return;
            }

            $this->testParticipantStatusses[$testParticipant->uuid] = [
                'ratingStatus' => RatingStatus::get(
                    $testParticipant->answer_to_rate,
                    $testParticipant->answer_rated,
                    $this->testParticipantsFinishedWithRatingPercentage
                )
            ];
        });

        $abnormalitiesTotal = $this->testParticipants->sum(fn($tp) => ($tp->active && isset($tp->abnormalities)) ? $tp->abnormalities : 0);
        $abnormalitiesCount = $this->testParticipants->sum(fn($tp) => $tp->active && isset($tp->abnormalities));
        $abnormalitiesAverage = ($abnormalitiesCount === 0) ? 0 : $abnormalitiesTotal / $abnormalitiesCount;

        $this->testParticipants->each(function ($testParticipant) use (&$abnormalitiesAverage) {
            if (!$testParticipant->active) {
                return;
            }

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
        $this->testParticipants = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($this->testTake->getKey(), $this->testTake->discussing_question_id);

        $this->testParticipantCount = $this->testParticipants->count();
        $this->testParticipantCountActive = $this->testParticipants->sum(fn($tp) => $tp->active);

        $this->handleTestParticipantStatusses();
    }


    protected function getNavigationData()
    {
        $this->questionIndex = 0;
        $this->questionIndexOpenOnly = 0;

        if (!isset($this->testTake->discussing_question_id)) {
            return false;
        }

        if($this->questionsOrderList->get($this->testTake->discussing_question_id)) {
            $this->questionIndex = $this->questionsOrderList->get($this->testTake->discussing_question_id)['order'];
            $this->questionIndexOpenOnly = $this->questionsOrderList->get($this->testTake->discussing_question_id)['order_open_only'] ?: $this->questionIndexOpenOnly;
        }
    }

    protected function getStaticNavigationData()
    {
        $this->openOnly = $this->testTake->discussion_type === self::DISCUSSION_TYPE_OPEN_ONLY;

        $this->questionsOrderList = collect($this->testTake->test->getQuestionOrderListWithDiscussionType());

        $this->questionCount = $this->questionsOrderList->count('id');

        //filter questions that have 'discuss in class' on false
        $this->questionsOrderList = $this->questionsOrderList->filter(fn($item) => $item['discuss'] === 1);

        if ($this->testTake->discussion_type === self::DISCUSSION_TYPE_OPEN_ONLY) {
            $this->questionsOrderList = $this->questionsOrderList->filter(fn($item) => $item['question_type'] === 'OPEN');
        }
        $this->questionCountFiltered = $this->questionsOrderList->count('id');

        $this->firstQuestionId = $this->questionsOrderList->sortBy('order')->first()['id'];
        $this->lastQuestionId = $this->questionsOrderList->sortBy('order')->last()['id'];

        $this->getNavigationData();
    }

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

        if($this->previousQuestionId !== null) {
            return $this->testTake->update(['discussing_question_id' => $this->previousQuestionId]);
        }
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
    protected function setActiveAnswerAnsweredStatus()
    {
        if (!$this->activeAnswerRating->answer->isAnswered) {
            $this->activeAnswerAnsweredStatus = 'not-answered';
            return;
        }
        if ($this->testTake->discussingQuestion instanceof CompletionQuestion) {
            $givenAnswersCount = collect(json_decode($this->activeAnswerRating->answer->json, true))->count();
            $this->activeAnswerAnsweredStatus = (
                $givenAnswersCount === $this->completionQuestionTagCount
            )
                ? 'answered'
                : 'partly-answered';
            return;
        }
        $this->activeAnswerAnsweredStatus = 'answered';
    }

    protected function setActiveAnswerText(): void
    {
        if ($this->testTake->discussingQuestion instanceof CompletionQuestion) {
            $this->activeAnswerText = $this->convertCompletionQuestionToHtml(
                $this->uniformCompletionQuestionAnswersDataObject('answers')
            );
            return;
        }
        if ($this->testTake->discussingQuestion instanceof DrawingQuestion) {

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
            ->join('group_question_questions', 'group_question_questions.group_question_id', '=', 'test_questions.question_id')
            ->where('test_takes.id', '=', $this->testTake->getKey())
            ->where('group_question_questions.question_id', '=', $questionId)->first()?->group_question_id;
    }

    private function redirectIfNotAllowed(): Redirector|null
    {
        if (!in_array(
            $this->testTake->test_take_status_id, [
            TestTakeStatus::STATUS_TAKEN,
            TestTakeStatus::STATUS_DISCUSSING,
            TestTakeStatus::STATUS_DISCUSSED,
            TestTakeStatus::STATUS_RATED,
        ])) {
            return redirect()->route('teacher.test-takes', ['stage' => 'taken']);
        }
        return null;
    }

    private function testTakeIsBeingRestarted(): bool
    {
        return $this->coLearningHasBeenStarted === false
            && $this->testTake->discussing_question_id !== null
            && $this->testTake->discussion_type !== null
            && $this->testTake->test_take_status_id >= TestTakeStatus::STATUS_DISCUSSING;
    }

    private function testTakeHasNotYetBeenStartedBefore(): bool
    {
        return $this->testTake->discussing_question_id === null
            || $this->testTake->discussion_type === null
            || $this->testTake->test_take_status_id === TestTakeStatus::STATUS_TAKEN;
    }

    private function testTakeStatusNeedsToBeUpdated(): bool
    {
        return $this->testTake->test_take_status_id !== TestTakeStatus::STATUS_DISCUSSING;
    }

    private function discussionTypeNeedsToBeUpdated(string $testTakeDiscussionType): bool
    {
        return $this->testTake->discussion_type !== $testTakeDiscussionType;
    }

    private function deleteStudentAnswerRatings()
    {
        AnswerRating::where('test_take_id', '=', $this->testTake->getKey())
            ->where('type', '=', 'STUDENT')
            ->delete();
    }

    public function handleHeaderCollapse($args)
    {
        return $this->startCoLearningSession($args['discussionType']);
    }
}
