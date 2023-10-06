<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Collection;
use tcCore\Answer;
use tcCore\Http\Livewire\EvaluationComponent;
use tcCore\Http\Traits\WithInlineFeedback;

class TestReview extends EvaluationComponent
{
    /*Template properties*/
    public string $reviewableUntil = '';

    /*Query string properties*/
    protected $queryString = ['questionPosition' => ['except' => '', 'as' => 'q']];
    public string $questionPosition = '';

    public Collection $anonymousStudentNames;

    public function mount($testTakeUuid): void
    {
        $this->testTakeUuid = $testTakeUuid;

        $this->setData();

        $this->start();

        $this->setTemplateVariables();

        $this->skipBooted = true;
    }

    public function booted(): void
    {
        if ($this->skipBooted) {
            return;
        }

        $this->setData();
        $this->hydrateCurrentProperties();
    }

    public function render()
    {
        return view('livewire.student.test-review')->layout('layouts.base');
    }

    /* Computed properties */
    public function getShowScoreSliderProperty(): bool
    {
        return true;
    }

    public function getShowCoLearningScoreToggleProperty(): bool
    {
        return $this->studentRatings()->isNotEmpty();
    }

    public function getShowCorrectionModelProperty(): bool
    {
        return $this->testTakeData->fresh()->show_correction_model;
    }

    /* Public accessible methods */
    public function redirectBack()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function loadQuestion(int $position): bool
    {
        $index = $position - 1;

        $this->currentQuestion = $this->questions->get($index);
        $this->currentAnswer = $this->answers->where('question_id', $this->currentQuestion->id)->first();
        $this->questionPosition = $position;
        $this->handleGroupQuestion();
        $this->handleAnswerFeedback();
        $this->openClosedPanels();
        $this->score = $this->handleAnswerScore();

        $this->getSortedAnswerFeedback();
        $this->studentNumbers();

        return true;
    }

    protected function currentAnswerCoLearningRatingsHasNoDiscrepancy(): bool
    {
        return !$this->currentAnswer->hasCoLearningDiscrepancy();
    }

    protected function currentAnswerHasToggleDiscrepanciesInCoLearningRatings(): bool
    {
        if(!$this->currentAnswer->hasCoLearningDiscrepancy()) {
            return false;
        }
        return !!$this->currentAnswer->discrepancyInToggleData;
    }

    public function finalAnswerReached(): bool
    {
        return $this->answers->count() === (int)$this->questionPosition;
    }

    public function next(): bool
    {
        $this->loadQuestion((int)$this->questionPosition + 1);
        return true;
    }

    public function previous(): bool
    {
        $this->loadQuestion((int)$this->questionPosition - 1);
        return true;
    }

    /* Private methods */
    protected function setData(): void
    {
        $this->testTakeData = $this->getTestTakeData();

        $this->answers = $this->getAnswers();

        $this->groups = $this->getGroups();

        $this->questions = $this->getQuestions();

        $this->addGroupConnectorPropertyToAnswerIfNecessary();
    }

    protected function start(): void
    {
        $this->initializeNavigationProperties();

        $this->loadQuestion($this->questionPosition);
    }

    protected function setTemplateVariables(): void
    {
        $this->testName = $this->testTakeData->test->name;
        $this->reviewableUntil = $this->testTakeData->show_results->translatedFormat('j F \'y - H:i');
    }

    protected function initializeNavigationProperties(): void
    {
        $this->questionPosition = blank($this->questionPosition) ? 1 : $this->questionPosition;
    }

    protected function hydrateCurrentProperties(): void
    {
        $this->currentQuestion = $this->questions->get((int)$this->questionPosition - 1);
    }

    /**
     * @return void
     */
    private function addGroupConnectorPropertyToAnswerIfNecessary(): void
    {
        $this->questions
            ->whereNotNull('belongs_to_groupquestion_id')
            ->groupBy('belongs_to_groupquestion_id')
            ->each(fn($group) => $group->pop())
            ->flatten()
            ->each(function ($question) {
                $this->answers
                    ->first(fn($answer) => $answer->question_id === $question->id)
                    ->connector = true;
            });
    }

    private function handleAnswerFeedback(): void
    {
        $this->hasFeedback = $this->currentAnswer->feedback->isNotEmpty();
    }

    protected function handleAnswerScore(): null|int|float
    {
        return $this->currentAnswer->calculateFinalRating();
    }

    protected function getTestTakeData(): \tcCore\TestTake
    {
        $userId = auth()->id();
        return cache()
            ->remember(
                sprintf("review-data-%s-%s", $this->testTakeUuid, $userId),
                now()->addDays(3),
                function () use ($userId) {
                    return \tcCore\TestTake::whereUuid($this->testTakeUuid)
                        ->with([
                            'test:id,name',
                            'test.testQuestions:id,test_id,question_id',
                            'test.testQuestions.question',
                            'testParticipants' => fn($query) => $query->where('user_id', $userId),
                            'testParticipants.answers',
                            'testParticipants.answers.answerRatings',
                            'testParticipants.answers.feedback'
                        ])
                        ->first();
                }
            );
    }

    protected function getAnswers(): Collection
    {
        return $this->testTakeData->testParticipants
            ->load('answers.feedback')
            ->flatMap(fn($participant) => $participant->answers->map(fn($answer) => $answer))
            ->sortBy(['order', 'test_participant_id'])
            ->values();
    }

    protected function getQuestions(): Collection
    {
        return $this->testTakeData
            ->test
            ->getFlatQuestionList()
            ->filter(fn($question) => $this->answers->pluck('question_id')->contains($question->id))
            ->each(function ($question) {
                $question->sortOrder = $this->answers->search(fn($a) => $a->question_id === $question->id);
            })
            ->sortBy(['sortOrder'])
            ->values();
    }

    private function openClosedPanels(): void
    {
        $this->questionPanel = true;
        $this->answerPanel = true;
    }

    public function loadQuestionFromNav($position): bool
    {
        $this->loadQuestion($position);
        return true;
    }

    public function studentNumbers()
    {
        $studentRatingUserIds = $this->studentRatings()
            ?->map->user_id
            ->unique() ?? collect();

        $answerFeedbackUserIds = $this->answerFeedback
            ?->filter(fn($feedback) => $feedback->user->isA('student'))
            ?->map->user_id
            ->unique() ?? collect();

        $answerFeedbackUserIds->each(fn($userId) => $studentRatingUserIds->doesntContain($userId) ? $studentRatingUserIds->push($userId) : null);

        $this->anonymousStudentNames = $studentRatingUserIds->mapWithKeys(function ($userId, $key) {
            return [
                $userId => sprintf('%s %s', __('auth.Student'), $key + 1)
            ];
        });
    }
}