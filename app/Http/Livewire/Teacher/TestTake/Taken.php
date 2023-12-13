<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Redirector;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Attainment;
use tcCore\BaseAttainment;
use tcCore\Http\Enums\GradingStandard;
use tcCore\Http\Enums\TestTakeEventTypes;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\Normalize;
use tcCore\Http\Helpers\TestTakeHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;
use tcCore\TestTakeStatus;
use tcCore\User;
use tcCore\UserFeatureSetting;

class Taken extends TestTakeComponent
{
    public array $takenTestData = [];
    public int $testTakeStatusId;

    public bool $showWaitingRoom = false;
    public bool $showStudentNames = true;
    public bool $reviewActive = false;
    public bool $assessmentDone = false;

    public Collection $participantResults;

    public ?Collection $attainments;
    public int $maxQuestionsForAttainmentAnalysis = 20;
    public array $analysisQuestionValues = [0, 5, 10, 20, 40, 80, 160];
    public Collection $attainmentValueRatios;

    protected Collection $gradingStandards;
    public string|GradingStandard $gradingStandard;
    public string|int|float $gradingValue = 1;
    public null|string|int|float $cesuurPercentage = null;
    public bool $showGradeToStudent;

    private Collection $questionsOfTest;
    public array $questionsToIgnore = [];
    public Collection $participantScoreOverrides;
    public bool $participantGradesChanged = false;

    public string $standardizeTabDirection = 'asc';
    public string $resultsTabDirection = 'asc';

    public ?int $gradingDiffKey = null;


    protected function getRules(): array
    {
        return ['participantResults.*.rating' => 'int'];
    }

    /* Lifecycle methods */
    public function mount(TestTakeModel $testTake): void
    {
        if (Gate::denies('canUseTakenTestPage')) {
            TestTakeModel::redirectToDetail($testTake->uuid);
        }

        $asExamCoordinator = false;
        if($testTake->test_take_status_id === TestTakeStatus::STATUS_RATED){
            $asExamCoordinator = true;
        }
        Gate::authorize('isAllowedToViewTestTake',[$testTake, false, $asExamCoordinator ]);

        parent::mount($testTake);
        $this->createSystemRatingsWhenNecessary();
        $this->testTakeStatusId = $this->testTake->test_take_status_id;
        $this->clearSession();
        $this->setParticipantResults();
        $this->setTakenTestData();
        $this->showWaitingRoom = in_array(
            $this->testTakeStatusId,
            [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING]
        );

        $this->attainments = $this->getAttainments();
        $this->assessmentDone = $this->takenTestData['assessedQuestions'] === $this->takenTestData['questionsToAssess'];
        $this->reviewActive = $this->testTake->review_active;
        $this->showGradeToStudent = $this->testTake->show_grades;
        $this->participantScoreOverrides = collect();

        if ($this->showStandardization()) {
            $this->gradingDiffKey = rand(11111,99999);
            $this->setStandardizationProperties();
        }
    }

    public function updatedReviewActive(bool $value): void
    {
        TestTakeModel::whereUuid($this->testTakeUuid)->update(['review_active' => $value]);
    }

    public function updatedShowStudentNames(): void
    {
        $this->participantResults->each(function ($participant, $key) {
            $participant->name = $this->getDisplayNameForParticipant($participant->user, $key);
        });
    }

    public function updatedParticipantResults($value, $name): void
    {
        if (!str($name)->contains('rating')) {
            return;
        }
        $key = str($name)->explode('.')->first();
        if ($participantUuid = $this->participantResults->get($key)?->uuid) {
            $this->participantScoreOverrides->put($participantUuid, $value);
        }

        Session::put($this->resultSessionKey(), $this->participantResults);
        $this->setParticipantsGradeChangedNotification();
    }

    public function updatedGradingValue($value): void
    {
        $this->standardizeResults(
            GradingStandard::tryFrom($this->gradingStandard),
            $value
        );
        $this->setParticipantsGradeChangedNotification();
        $this->gradingDiffKey = rand(11111,99999);
    }

    public function updatedGradingStandard($value): void
    {
        $this->standardizeResults(GradingStandard::tryFrom($value));
        $this->setParticipantsGradeChangedNotification();
        $this->gradingDiffKey = rand(11111,99999);
    }

    public function updatedCesuurPercentage($value): void
    {
        $this->validate(['cesuurPercentage' => 'filled|numeric|min:1|max:100']);

        $this->standardizeResults(
            GradingStandard::tryFrom($this->gradingStandard),
            $this->gradingValue
        );

        $this->setParticipantsGradeChangedNotification();
        $this->gradingDiffKey = rand(11111,99999);
    }

    public function updatedShowGradeToStudent($value): void
    {
        $this->testTake->update(['show_grades' => $value]);
    }

    public function hydrate()
    {
        parent::hydrate();
        $this->setParticipantResults();
        $this->gradingStandards = GradingStandard::casesWithDescription();
        $this->questionsOfTest = Session::get(
            $this->questionsSessionKey(),
            $this->getQuestionList()
        );
    }

    /* Public methods */
    public function refresh(): void
    {
        $this->fillGridData();
        $this->setStudentData();
        $this->setInvigilators();
        $this->reviewActive = $this->testTake->fresh()->review_active;
    }

    public function redirectToOverview(): Redirector|RedirectResponse
    {
        return $this->testTakeStatusId === TestTakeStatus::STATUS_RATED
            ? CakeRedirectHelper::redirectToCake('results.rated')
            : redirect()->route('teacher.test-takes', 'taken');
    }

    public function getButtonType(string $context): string
    {
        $contexts = [
            TestTakeStatus::STATUS_TAKEN      => [
                'CO-Learning' => 'cta',
                'Assessment'  => 'primary'
            ],
            TestTakeStatus::STATUS_DISCUSSING => [
                'CO-Learning' => 'cta',
                'Assessment'  => 'primary'
            ],
            TestTakeStatus::STATUS_DISCUSSED  => [
                'CO-Learning' => 'primary',
                'Assessment'  => 'cta'
            ],
        ];
        return $contexts[$this->testTakeStatusId][$context];
    }

    public function showResultsButtonText(): string
    {
        return $this->testTake->show_results
            ? $this->testTake->show_results->format('d-m-Y')
            : __('test-take.Instellen');
    }

    public function breadcrumbTitle(): string
    {
        return $this->testTakeStatusId === TestTakeStatus::STATUS_RATED
            ? __('navigation.results')
            : __('header.Afgenomen');
    }

    public function initializingPresenceChannel($event): void
    {
        parent::initializingPresenceChannel($event);
        if (!$this->showWaitingRoom) {
            $this->skipRender();
        }
    }

    public function canPublishResults(): bool
    {
        if ($this->needsToPublishResults()) {
            return true;
        }
        return $this->participantGradesChanged;
    }

    public function publishButtonLabel(): string
    {
        return $this->testTake->results_published
            ? __('test-take.Resultaten opnieuw publiceren')
            : __('test-take.Resultaten publiceren');
    }

    public function showStandardization(): bool
    {
        return $this->assessmentDone && $this->showResults();
    }

    public function showResults(): bool
    {
        return $this->testTakeStatusId >= TestTakeStatus::STATUS_TAKEN;
    }

    public function changeStandardizeParticipantOrder(): void
    {
        $this->standardizeTabDirection = $this->standardizeTabDirection === 'asc' ? 'desc' : 'asc';
    }

    public function changeResultsParticipantOrder(): void
    {
        $this->resultsTabDirection = $this->resultsTabDirection === 'asc' ? 'desc' : 'asc';
    }

    public function needsToPublishResults(): bool
    {
        if (!$this->testTake->results_published) {
            return true;
        }
        if ($this->testTake->results_published->gt($this->testTake->assessed_at)) {
            return false;
        }

        return $this->participantResults->contains(function ($participant) {
            return ((float)$participant->rating !== (float)$participant->definitiveRating);
        });
    }

    public function testTakeHasNotFinishedDiscussing(): bool
    {
        return in_array($this->testTakeStatusId, [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING]);
    }

    public function testTakeIsDiscussedButNotCompletelyAssessed(): bool
    {
        return $this->testTakeStatusId === TestTakeStatus::STATUS_DISCUSSED && !$this->assessmentDone;
    }

    /* Button actions */
    public function startCoLearning(): Redirector|RedirectResponse|bool
    {
        if(!$this->takenTestData['colearnable']){
            $this->dispatchBrowserEvent('notify', ['message' => __('test-take.CO-Learning niet mogelijk met alleen carrousel vragen'), 'type' => 'error']);
            return true;
        }

        if (!$this->showWaitingRoom) {
            return $this->showWaitingRoom = true;
        }

        $coLearningRoute = route('teacher.co-learning', ['test_take' => $this->testTakeUuid, 'started' => 'false']);
        if ($this->testTake->assessed_at && $this->assessmentDone) {
            $this->emit(
                'openModal',
                'teacher.test-take.start-co-learning-after-assessment-modal',
                ['continue' => $coLearningRoute]
            );
            return false;
        }
        return redirect($coLearningRoute);
    }

    public function startAssessment(): Redirector|RedirectResponse|bool
    {
        if (!$this->testTake->results_published) {
            return redirect()->route('teacher.assessment', $this->testTakeUuid);
        }

        $this->emit(
            'openModal',
            'teacher.test-take.start-assessment-after-grading-modal',
            ['continue' => route('teacher.assessment', $this->testTakeUuid)]
        );
        return false;
    }

    public function assessParticipant(string $participantUuid): Redirector|RedirectResponse
    {
        return redirect()->route(
            'teacher.assessment',
            ['testTake' => $this->testTakeUuid, 'participant' => $participantUuid]
        );
    }

    public function attainmentStudents(BaseAttainment $attainment): array
    {
        return $this->addAdditionalPropertiesForRendering(
            $attainment->getStudentAnalysisDataForTestTake($this->testTake)
        )->toArray();
    }

    public function clearSession(): void
    {
        Session::forget($this->resultSessionKey());
    }

    public function toggleQuestionToIgnore($questionUuid): void
    {
        $this->questionsToIgnore[] = $questionUuid;
        if ($this->hasIgnoredAllQuestions()) {
            $this->addError('all_questions_ignored', 'Alle vragen zijn overgeslagen.');
            return;
        }

        $this->standardizeResults(
            GradingStandard::tryFrom($this->gradingStandard),
            $this->gradingValue
        );
        $this->setParticipantsGradeChangedNotification();
    }

    public function publishResults(): void
    {
        if (!$this->gradingStandard) {
            return;
        }
        $this->standardizeResults(
            standard    : GradingStandard::tryFrom($this->gradingStandard),
            gradingValue: $this->gradingValue,
            save        : true,
        );

        $this->participantResults->each(function ($participant) {
            if ($scoreOverride = $this->participantScoreOverrides->get($participant->uuid)) {
                $participant->rating = $scoreOverride;
                TestParticipant::whereId($participant->id)->update(['rating' => $scoreOverride]);
            }
            $participant->definitiveRating = $participant->rating;
        });

        if ($this->testTake->test_take_status_id !== TestTakeStatus::STATUS_RATED) {
            $this->testTake->updateToRated();
        }

        Session::put($this->resultSessionKey(), $this->participantResults);
        $this->participantGradesChanged = false;
    }

    /* Protected methods */
    protected function setTakenTestData(): void
    {
        $this->questionsOfTest = $this->getQuestionList();
        Session::put($this->questionsSessionKey(), $this->questionsOfTest);

        $this->takenTestData = [
            'questionCount'      => $this->questionsOfTest->count(),
            'discussedQuestions' => $this->discussedQuestions(),
            'assessedQuestions'  => $this->assessedQuestions(),
            'questionsToAssess'  => $this->questionsOfTest->count(),
            'maxScore'           => $this->questionsOfTest->sum('score'),
            'colearnable'        => $this->isColearnable(),
        ];
    }

    private function isColearnable(): bool
    {
        return $this->questionsOfTest->contains(function($question){
          return $question->colearnable === true;
        });
    }

    private function discussedQuestions(): int
    {
        if ($this->testTake->test_take_status_id < 7) {
            return 0;
        }

        return TestTakeHelper::getDiscussedQuestionCount($this->testTake);
    }

    private function questionsToAssess(): int
    {
        return Answer::whereIn(
            'test_participant_id',
            TestParticipant::where('test_take_id', $this->testTake->id)->select('id')
        )->count();
    }

    private function assessedQuestions(): int
    {
        return TestTakeHelper::getAssessedQuestionCount($this->testTake);
    }

    private function createSystemRatingsWhenNecessary(): void
    {
        $hasNoSystemRatings = AnswerRating::whereTestTakeId($this->testTake->id)
            ->whereType(AnswerRating::TYPE_SYSTEM)
            ->doesntExist();

        if ($hasNoSystemRatings) {
            foreach ($this->testTake->testParticipants as $participant) {
                AnswerChecker::checkAnswerOfParticipant($participant);
            }
        }
    }

    private function setParticipantResults(): void
    {
        if ($sessionResults = Session::get($this->resultSessionKey(), false)) {
            $this->participantResults = $sessionResults;
            return;
        }

        $this->testTake->loadMissing([
            'testParticipants',
            'testParticipants.user' => function ($query) {
                $query->select(
                    'id',
                    'name',
                    'name_first',
                    'name_suffix',
                    'uuid',
                    'time_dispensation',
                    'text2speech'
                )->withTrashed();
            },
            'testParticipants.answers',
            'testParticipants.answers.answerRatings',
            'testParticipants.testTakeEvents'
        ]);

        $this->participantResults = $this->testTake
            ->testParticipants
            ->each(function ($participant, $key) {
                $participant->user->setAppends([]);
                $participant->name = $this->getDisplayNameForParticipant($participant->user, $key);
                $participant->score = $this->getScoreForParticipant($participant);
                $participant->discrepancies = $this->getDiscrepanciesForParticipant($participant);
                $participant->rated = $this->getRatedQuestionsForParticipant($participant);
                $participant->testNotTaken = $participant->test_take_status_id < TestTakeStatus::STATUS_TAKEN;
                $participant->contextIcons = $this->getContextIconsForParticipant($participant);
                $participant->definitiveRating = $participant->rating;
            });

        Session::put($this->resultSessionKey(), $this->participantResults);
    }

    private function getScoreForParticipant(TestParticipant $participant): mixed
    {
        return $participant->answers->sum(fn($answer) => $answer->calculateFinalRating());
    }

    private function getDiscrepanciesForParticipant(TestParticipant $participant)
    {
        return $participant->answers->sum(function ($answer) {
            return (int)$answer->hasCoLearningDiscrepancy();
        });
    }

    private function getRatedQuestionsForParticipant(TestParticipant $participant): int
    {
        return $participant->answers
            ->sum(function ($answer) {
                $givenRating = (int)$answer->answerRatings
                    ->whereIn('type', [AnswerRating::TYPE_TEACHER, AnswerRating::TYPE_SYSTEM])
                    ->isNotEmpty();

                if ($givenRating) {
                    return $givenRating;
                }

                return (int)($answer->hasCoLearningDiscrepancy() === false);
            });
    }

    private function getAttainments(): Collection
    {
        $attainments = BaseAttainment::getAnalysisDataForTestTake($this->testTake);
        $this->setAttainmentAnalysisProperties($attainments);
        return $this->addAdditionalPropertiesForRendering($attainments);
    }

    private function setAttainmentAnalysisProperties(Collection $attainments): void
    {
        $this->maxQuestionsForAttainmentAnalysis = $this->getMaxQuestionsForAttainmentAnalysis($attainments);
        $maxAmountIndex = array_search(
            $this->maxQuestionsForAttainmentAnalysis,
            $this->analysisQuestionValues
        );
        $this->analysisQuestionValues = array_slice(
            $this->analysisQuestionValues,
            0,
            $maxAmountIndex + 1
        );

        $this->attainmentValueRatios = collect($this->analysisQuestionValues)->map(function ($section, $key) {
            return [
                'start'          => $section,
                'end'            => $section === 0 ? 5 : $section + $section,
                'multiplierBase' => $key,
            ];
        });
    }

    private function getMaxQuestionsForAttainmentAnalysis(Collection $attainments): int
    {
        $max = max($attainments->max('questions_per_attainment'), 20);
        if ($max > 20 && $max <= 40) {
            $max = 40;
        }
        if ($max > 40 && $max <= 80) {
            $max = 80;
        }
        if ($max > 80 /*&& $max <= 160*/) {
            $max = 160;
        }
        return $max;
    }

    private function addAdditionalPropertiesForRendering(Collection $models): Collection
    {
        $models->each(function ($model, $key) {
            $model->multiplier = $this->getLengthMultiplier($model);
            $model->display_pvalue = round($model->p_value * 100);
            $model->title = sprintf(
                '%s %s',
                trans_choice('test-take.pvalue_title_1', $model->display_pvalue),
                trans_choice('test-take.pvalue_title_2', $model->questions_per_attainment)
            );
            if ($model instanceof User) {
                $model->name = $this->getDisplayNameForParticipant($model, $key);
            }
        });
        return $models;
    }

    private function getLengthMultiplier(Attainment|BaseAttainment|User $model): mixed
    {
        $section = $this->getValueSection($model);
        return $section['multiplierBase'] + (
                ($model->questions_per_attainment - $section['start']) / ($section['end'] - $section['start'])
            );
    }

    private function getValueSection(Attainment|BaseAttainment|User $model)
    {
        return $this->attainmentValueRatios->where('start', '<', $model->questions_per_attainment)
            ->where('end', '>=', $model->questions_per_attainment)
            ->first();
    }

    private function getContextIconsForParticipant(TestParticipant $participant): Collection
    {
        $icons = $participant->testTakeEvents
            ->where('test_take_event_type_id', TestTakeEventTypes::StartTest->value)
            ->mapWithKeys(function ($event) {
                if (Arr::get($event, 'metadata.device') === 'app') {
                    return ['app-logo' => __('test-take.Gemaakt in app')];
                }
                return ['web' => __('test-take.Gemaakt in browser')];
            })
            ->unique();

        if ($participant->user->time_dispensation) {
            $icons['time-dispensation'] = __('test-take.Tijd dispensatie van toepassing');
        }

        if ($participant->user->active_text2speech) {
            $icons['text2speech'] = __('test-take.Lees voor functies aan');
        }

        if ($participant->invigilator_note) {
            $icons['notepad'] = __('test-take.Notities aanwezig');
        }

        return $icons;
    }

    private function getDisplayNameForParticipant($user, $key): string
    {
        $studentName = $user->trashed() ? __('student.Deleted student') : html_entity_decode($user->name_full);
        return $this->showStudentNames
            ? $studentName
            : sprintf('Student %s', $key + 1);
    }

    private function resultSessionKey(): string
    {
        return '_participant_results_' . $this->testTakeUuid;
    }

    private function questionsSessionKey(): string
    {
        return '_questions_' . $this->testTakeUuid;
    }

    private function buildNormalizeRequest(GradingStandard $standard, bool $save = false): Collection
    {
        $questionsToIgnore = $this->questionsOfTest->map(function ($question) {
            return in_array($question->uuid, $this->questionsToIgnore) ? $question->id : null;
        })->filter()->toArray();

        $request = collect([
            'ignore_questions' => $questionsToIgnore,
            'preview'          => !$save,
        ]);
        return match ($standard) {
            GradingStandard::GOOD_PER_POINT   => $request->put('ppp', $this->gradingValue),
            GradingStandard::ERRORS_PER_POINT => $request->put('epp', $this->gradingValue),
            GradingStandard::AVERAGE          => $request->put('wanted_average', $this->gradingValue),
            GradingStandard::N_TERM           => $request->put('n_term', $this->gradingValue),
            GradingStandard::CESUUR           => $this->fillCesuurRequest($request),
        };
    }

    private function fillCesuurRequest(Collection $request): Collection
    {
        return $request->put('n_term', $this->gradingValue)
            ->put('pass_mark', $this->cesuurPercentage);
    }

    private function standardizeResults(GradingStandard $standard, $gradingValue = null, bool $save = false): void
    {
        if (!$gradingValue) {
            $this->gradingValue = $standard->initialValue();
            if ($standard === GradingStandard::CESUUR) {
                $this->gradingValue = GradingStandard::N_TERM->initialValue();
                $this->cesuurPercentage = $this->cesuurPercentage ?? GradingStandard::CESUUR->initialValue();
            }
        }

        $normalize = new Normalize($this->testTake, $this->buildNormalizeRequest($standard, $save));

        $data = match ($standard) {
            GradingStandard::GOOD_PER_POINT   => $normalize->normBasedOnGoodPerPoint(),
            GradingStandard::ERRORS_PER_POINT => $normalize->normBasedOnErrorsPerPoint(),
            GradingStandard::AVERAGE          => $normalize->normBasedOnAverageMark(),
            GradingStandard::N_TERM           => $normalize->normBasedOnNTerm(),
            GradingStandard::CESUUR           => $normalize->normBasedOnNTermAndPassMark(),
        };

        $this->participantResults->each(fn($participant) => $participant->rating = $data[$participant->id] ?? 0);

        $this->dispatchBrowserEvent('clear-used-sliders');
    }

    private function getQuestionList(): Collection
    {
        $hasCarousel = false;
        $allQuestions = $this->testTake
            ->loadMissing([
                'test',
                'test.testQuestions',
                'test.testQuestions.question',
                'test.testQuestions.question.pValue',
            ])
            ->test
            ->getFlatQuestionList(function ($connection, $groupQuestion = null) use(&$hasCarousel) {
                $connection->question->pValues = $connection->question
                    ->pValue
                    ->filter(
                        fn($pValue) => $this->participantResults->pluck('id')->contains($pValue->test_participant_id)
                    );
                $colearnable = true;
                if($groupQuestion && $groupQuestion->isCarouselQuestion()){
                    $colearnable = false;
                    $hasCarousel = true;
                }
                $connection->question->colearnable = $colearnable;
            })
            ->when($this->testTakeStatusId >= TestTakeStatus::STATUS_DISCUSSED, function ($collection) {
                $collection->each(function ($question, $key) {
                    $question->order = $key + 1;
                    $question->pValuePercentage = null;
                    $question->pValueAverage = null;
                    $question->pValueMaxScore = null;
                    if (!$question->isType('Infoscreen') && $question->pValues->isNotEmpty()) {
                        $question->pValuePercentage = (
                                $question->pValues->sum('score') / $question->pValues->sum('max_score')
                            ) * 100;
                        $question->pValueAverage = $question->pValues->avg('score');
                        $question->pValueMaxScore = $question->pValues->avg('max_score');
                    }
                });
            });

        if($hasCarousel) {
            // we need to remove the ones that are not given to test participants as some carousel
            // questions can be left out
            $testParticipantIdQueryBuilder = $this->testTake->testParticipants()
                ->where('test_take_status_id', '>', TestTakeStatus::STATUS_TEST_NOT_TAKEN)
                ->select('id');
            $questionIdsUsed = Answer::whereIn('test_participant_id', $testParticipantIdQueryBuilder)->groupBy('question_id')->pluck('question_id');
            return $allQuestions->filter(function ($question) use ($questionIdsUsed) {
                return $questionIdsUsed->contains($question->getKey());
            });
        }
        return $allQuestions;
    }

    private function hasIgnoredAllQuestions(): bool
    {
        return count($this->questionsToIgnore) === ($this->takenTestData['questionCount'] - 1);
    }

    private function setStandardizationProperties(): void
    {
        $this->calculateFinalRatingForParticipantsWhenNecessary();

        $this->gradingStandards = GradingStandard::casesWithDescription();

        if ($this->testTake->results_published) {
            [$value, $standard, $cesuurPercentage] = GradingStandard::getEnumFromTestTake($this->testTake);
        } else {
            [$standard, $value, $cesuurPercentage] = $this->getDefaultStandardizationProperties();
        }

        $this->gradingValue = (float)$value;
        $this->gradingStandard = str($standard->name)->lower()->value();
        $this->cesuurPercentage = $cesuurPercentage ? (float)$cesuurPercentage : null;


        if (!$this->testTake->results_published || $this->testTake->results_published->lt(
                $this->testTake->assessed_at
            )) {
            $this->standardizeResults(
                $standard,
                $this->gradingValue
            );
        }
    }

    /**
     * @return array
     */
    private function getDefaultStandardizationProperties(): array
    {
        $standard = UserFeatureSetting::getSetting(
            user   : auth()->user(),
            title  : UserFeatureSettingEnum::GRADE_DEFAULT_STANDARD,
            default: GradingStandard::N_TERM
        );
        $value = UserFeatureSetting::getSetting(
            user   : auth()->user(),
            title  : UserFeatureSettingEnum::GRADE_STANDARD_VALUE,
            default: $standard->initialValue()
        );
        if ($standard === GradingStandard::CESUUR) {
            $cesuurPercentage = UserFeatureSetting::getSetting(
                user   : auth()->user(),
                title  : UserFeatureSettingEnum::GRADE_CESUUR_PERCENTAGE,
                default: GradingStandard::CESUUR->initialValue()
            );
        }
        return [$standard, $value, $cesuurPercentage ?? null];
    }

    private function sortParticipantResults(?string $direction = null): Collection
    {
        if (!$direction) {
            return $this->participantResults;
        }
        return $this->participantResults->when(
            $direction === 'asc',
            fn($collection) => $collection->sortBy('name'),
            fn($collection) => $collection->sortByDesc('name'),
        );
    }

    private function calculateFinalRatingForParticipantsWhenNecessary(): void
    {
        Answer::whereIn('test_participant_id', $this->testTake->testParticipants()->select('id'))
            ->whereNull('final_rating')
            ->get()
            ->each
            ->calculateAndSaveFinalRating();
    }

    private function setParticipantsGradeChangedNotification(): void
    {
        if($this->testTake->results_published) {
            $this->participantGradesChanged = true;
        }
    }
}