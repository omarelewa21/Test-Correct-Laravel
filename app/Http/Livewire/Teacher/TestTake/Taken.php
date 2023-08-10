<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Livewire\Redirector;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Attainment;
use tcCore\Http\Enums\GradingStandard;
use tcCore\Http\Enums\TestTakeEventTypes;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Helpers\Normalize;
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
    public bool $showStudentNames = false;
    public bool $reviewActive = false;
    public bool $assessmentDone = false;

    public Collection $participantResults;

    public ?Collection $attainments;
    public int $maxQuestionsForAttainmentAnalysis = 20;
    public array $analysisQuestionValues = [0, 5, 10, 20, 40, 80, 160];
    public Collection $attainmentValueRatios;

    protected Collection $gradingStandards;
    public $gradingStandard;
    public int|float $gradingValue = 1;
    public $cesuurPercentage = null;
    public bool $showGradeToStudent;

    private Collection $questionsOfTest;
    public array $questionsToIgnore = [];


    protected function getRules(): array
    {
        return ['participantResults.*.rating' => 'int'];
    }

    public function mount(TestTakeModel $testTake): void
    {
        parent::mount($testTake);
        $this->createSystemRatingsWhenNecessary();
        $this->testTakeStatusId = $this->testTake->test_take_status_id;

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

        $this->setStandardizationProperties();
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

    public function updatedParticipantResults(): void
    {
        Session::put($this->resultSessionKey(), $this->participantResults);
    }

    public function updatedGradingValue($value): void
    {
        $this->standardizeResults(
            GradingStandard::tryFrom($this->gradingStandard),
            $value
        );
    }

    public function updatedGradingStandard($value): void
    {
        $this->standardizeResults(GradingStandard::tryFrom($value));
    }

    public function updatedCesuurPercentage($value): void
    {
        $this->validate(['cesuurPercentage' => 'filled|numeric|min:1|max:100']);

        $this->standardizeResults(
            GradingStandard::tryFrom($this->gradingStandard),
            $this->gradingValue
        );
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
        return redirect()->route('teacher.test-takes', 'taken');
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
        return __('header.Afgenomen');
    }

    public function initializingPresenceChannel($event): void
    {
        parent::initializingPresenceChannel($event);
        if (!$this->showWaitingRoom) {
            $this->skipRender();
        }
    }

    /* Button actions */
    public function startCoLearning(): Redirector|RedirectResponse|bool
    {
        if ($this->showWaitingRoom) {
            return redirect()->route('teacher.co-learning', ['test_take' => $this->testTakeUuid, 'started' => 'false']);
        }

        return $this->showWaitingRoom = true;
    }

    public function startAssessment(): Redirector|RedirectResponse
    {
        return redirect()->route('teacher.assessment', $this->testTakeUuid);
    }

    public function assessParticipant(string $participantUuid): Redirector|RedirectResponse
    {
        return redirect()->route(
            'teacher.assessment',
            ['testTake' => $this->testTakeUuid, 'participant' => $participantUuid]
        );
    }

    public function attainmentStudents(Attainment $attainment): array
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
    }

    public function publishResults(): void
    {
        $this->participantResults->each(function ($participant) {
            $participant->save();
        });
    }

    /* Protected methods */
    protected function setTakenTestData(): void
    {
        $this->questionsOfTest = $this->getQuestionList();
        Session::put($this->questionsSessionKey(), $this->questionsOfTest);

        $this->takenTestData = [
            'questionCount'      => $this->questionsOfTest->count(),
            'discussedQuestions' => $this->discussedQuestions($this->questionsOfTest),
            'assessedQuestions'  => $this->assessedQuestions(),
            'questionsToAssess'  => $this->questionsOfTest->count(),
            'maxScore'           => $this->questionsOfTest->sum('score')
        ];
    }

    private function discussedQuestions(Collection $questions): int
    {
        $index = $questions->search(function ($question) use ($questions) {
            return $question->id === $questions->where('id', $this->testTake->discussing_question_id)
                    ->first()?->id;
        });
        return $index !== false ? $index + 1 : 0;
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
        return Answer::select('answers.question_id')
            ->leftJoin(
                'test_participants',
                'test_participants.id',
                '=',
                'answers.test_participant_id'
            )
            ->where('test_participants.test_take_id', $this->testTake->getKey())
            ->whereNull('test_participants.deleted_at')
            ->whereExists(function ($query) {
                $query->select('answer_ratings.id')
                    ->from('answer_ratings')
                    ->whereRaw('answer_ratings.answer_id = answers.id')
                    ->where('answer_ratings.type', '!=', AnswerRating::TYPE_STUDENT)
                    ->whereNull('answer_ratings.deleted_at');
            })
            ->groupBy('answers.question_id')
            ->get()
            ->count();
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
            'testParticipants.user:id,name,name_first,name_suffix,uuid,time_dispensation,text2speech',
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
        return $participant->answers->sum(function ($answer) {
            $rating = $answer->answerRatings
                ->sortBy(function ($rating) {
                    if ($rating->type === AnswerRating::TYPE_TEACHER) {
                        return 1;
                    }
                    if ($rating->type === AnswerRating::TYPE_SYSTEM) {
                        return 2;
                    }
                    return 3;
                })->first();
            return $rating?->rating;
        });
    }

    private function getDiscrepanciesForParticipant(TestParticipant $participant)
    {
        return $participant->answers->sum(function ($answer) {
            return (int)$answer->hasCoLearningDiscrepancy();
        });
    }

    private function getRatedQuestionsForParticipant(TestParticipant $participant): int
    {
        return $participant->answers->sum(function ($answer) {
            return (int)$answer->answerRatings
                ->whereIn('type', [AnswerRating::TYPE_TEACHER, AnswerRating::TYPE_SYSTEM])
                ->isNotEmpty();
        });
    }

    private function getAttainments(): Collection
    {
        $attainments = Attainment::getAnalysisDataForTestTake($this->testTake);
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
        if ($max > 80 && $max <= 160) {
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

    private function getLengthMultiplier(Attainment|User $model): mixed
    {
        $section = $this->getValueSection($model);
        return $section['multiplierBase'] + (
                ($model->questions_per_attainment - $section['start']) / ($section['end'] - $section['start'])
            );
    }

    private function getValueSection(Attainment|User $model)
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
        return $this->showStudentNames
            ? html_entity_decode($user->name_full)
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

    private function buildNormalizeRequest(GradingStandard $standard): Collection
    {
        $questionsToIgnore = $this->questionsOfTest->map(function ($question) {
            return in_array($question->uuid, $this->questionsToIgnore) ? $question->id : null;
        })->filter()->toArray();

        $request = collect([
            'ignore_questions' => $questionsToIgnore,
            'preview'          => true,
        ]);
        return match ($standard) {
            GradingStandard::GOOD_PER_POINT => $request->put('ppp', $this->gradingValue),
            GradingStandard::ERRORS_PER_POINT => $request->put('epp', $this->gradingValue),
            GradingStandard::MEAN => $request->put('wanted_average', $this->gradingValue),
            GradingStandard::N_TERM => $request->put('n_term', $this->gradingValue),
            GradingStandard::CESUUR => $this->fillCesuurRequest($request),
        };
    }

    private function fillCesuurRequest(Collection $request): Collection
    {
        return $request->put('n_term', $this->gradingValue)
            ->put('pass_mark', $this->cesuurPercentage);
    }

    private function standardizeResults(GradingStandard $standard, $gradingValue = null): void
    {
        if (!$gradingValue) {
            $this->gradingValue = $standard->initialValue();
            if ($standard === GradingStandard::CESUUR) {
                $this->gradingValue = GradingStandard::N_TERM->initialValue();
                $this->cesuurPercentage = $this->cesuurPercentage ?? GradingStandard::CESUUR->initialValue();
            }
        }

        $normalize = new Normalize($this->testTake, $this->buildNormalizeRequest($standard));

        $data = match ($standard) {
            GradingStandard::GOOD_PER_POINT => $normalize->normBasedOnGoodPerPoint(),
            GradingStandard::ERRORS_PER_POINT => $normalize->normBasedOnErrorsPerPoint(),
            GradingStandard::MEAN => $normalize->normBasedOnAverageMark(),
            GradingStandard::N_TERM => $normalize->normBasedOnNTerm(),
            GradingStandard::CESUUR => $normalize->normBasedOnNTermAndPassMark(),
        };

        $this->participantResults->each(fn($participant) => $participant->rating = $data[$participant->id] ?? 0);

        $this->dispatchBrowserEvent('clear-used-sliders');
    }

    private function getQuestionList(): Collection
    {
        return $this->testTake
            ->loadMissing([
                'test',
                'test.testQuestions',
                'test.testQuestions.question',
                'test.testQuestions.question.pValue',
            ])
            ->test
            ->getFlatQuestionList(function ($connection) {
                $connection->question->pValues = $connection->question
                    ->pValue
                    ->filter(
                        fn($pValue) => $this->participantResults->pluck('id')->contains($pValue->test_participant_id)
                    );
            })
            ->each(function ($question, $key) {
                $question->order = $key + 1;
                $question->pValuePercentage = null;
                $question->pValueAverage = null;
                $question->pValueMaxScore = null;
                if (!$question->isType('Infoscreen')) {
                    $question->pValuePercentage = (
                            $question->pValues->sum('score') / $question->pValues->sum('max_score')
                        ) * 100;
                    $question->pValueAverage = $question->pValues->avg('score');
                    $question->pValueMaxScore = $question->pValues->avg('max_score');
                }
            });
    }

    private function hasIgnoredAllQuestions(): bool
    {
        return count($this->questionsToIgnore) === ($this->takenTestData['questionCount'] - 1);
    }

    private function setStandardizationProperties(): void
    {
        $this->gradingStandards = GradingStandard::casesWithDescription();
        $standard = UserFeatureSetting::getSetting(
            user   : auth()->user(),
            title  : UserFeatureSettingEnum::GRADE_DEFAULT_STANDARD,
            default: GradingStandard::N_TERM
        );
        $this->gradingStandard = str($standard->name)->lower()->value();

        $this->gradingValue = UserFeatureSetting::getSetting(
            user   : auth()->user(),
            title  : UserFeatureSettingEnum::GRADE_STANDARD_VALUE,
            default: $standard->initialValue()
        );

        if ($standard === GradingStandard::CESUUR) {
            $this->cesuurPercentage = UserFeatureSetting::getSetting(
                user   : auth()->user(),
                title  : UserFeatureSettingEnum::GRADE_CESUUR_PERCENTAGE,
                default: GradingStandard::CESUUR->initialValue()
            );
        }

        $this->standardizeResults(
            $standard,
            $this->gradingValue
        );
    }

}