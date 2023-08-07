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
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;
use tcCore\TestTakeStatus;
use tcCore\User;

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

    public $rating;
    protected function getRules(): array
    {
        return ['rating' => 'sometimes'];
    }

    public function mount(TestTakeModel $testTake): void
    {
        parent::mount($testTake);
        $this->createSystemRatingsWhenNecessary();
        $this->testTakeStatusId = $this->testTake->test_take_status_id;
        $this->setTakenTestData();
        $this->setStudentData();

        $this->showWaitingRoom = in_array(
            $this->testTakeStatusId,
            [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING]
        );
        $this->reviewActive = $this->testTake->review_active;
        $this->setParticipantResults();

        $this->attainments = $this->getAttainments();
        $this->assessmentDone = $this->takenTestData['assessedQuestions'] === $this->takenTestData['questionsToAssess'];
        $this->gradingStandards = GradingStandard::casesWithDescription();
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

    public function hydrate()
    {
        parent::hydrate();
        $this->setParticipantResults();
        $this->gradingStandards = GradingStandard::casesWithDescription();
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
            TestTakeStatus::STATUS_DISCUSSED  => [
                'CO-Learning' => 'primary',
                'Assessment'  => 'cta'
            ]
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

    /* Protected methods */
    protected function setTakenTestData(): void
    {
        $questionsOfTest = $this->testTake->test->getFlatQuestionList();

        $this->takenTestData = [
            'questionCount'      => $questionsOfTest->count(),
            'discussedQuestions' => $this->discussedQuestions($questionsOfTest),
            'assessedQuestions'  => $this->assessedQuestions(),
            'questionsToAssess'  => $questionsOfTest->count(),
            'maxScore'           => $questionsOfTest->sum('score')
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
        return AnswerRating::join('answers', 'answers.id', '=', 'answer_ratings.answer_id')
            ->where('answer_ratings.test_take_id', $this->testTake->id)
            ->where('answer_ratings.type', '!=', AnswerRating::TYPE_STUDENT)
            ->selectRaw('answers.question_id, count(answer_ratings.id) as timesRated')
            ->groupBy('answers.question_id')
            ->get()
            ->where('timesRated', $this->testTake->testParticipants->count())
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
            });

        Session::put($this->resultSessionKey(), $this->participantResults);
    }

    protected function getPusherListeners(): array
    {
        if ($this->showWaitingRoom) {
            return parent::getPusherListeners();
        }
        return [];
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
            return $rating->rating;
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

}