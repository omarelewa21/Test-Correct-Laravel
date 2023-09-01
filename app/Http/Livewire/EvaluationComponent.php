<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\AnswerFeedback;
use tcCore\AnswerRating;
use tcCore\Exceptions\AssessmentException;
use tcCore\Http\Enums\CommentEmoji;
use tcCore\Http\Enums\CommentMarkerColor;
use tcCore\Http\Traits\WithInlineFeedback;
use tcCore\TestTake;

abstract class EvaluationComponent extends TCComponent
{
    use WithInlineFeedback;

    /*Template properties*/
    public string $testName = '';
    public bool $questionPanel = true;
    public bool $answerPanel = true;
    public bool $answerModelPanel = true;
    public bool $groupPanel = true;

    /* Data properties filled from cache */
    protected $testTakeData;
    protected $answers;
    protected $groups;
    protected $questions;

    /* Context properties */
    public string $testTakeUuid;
    public $currentAnswer;
    public $currentQuestion;
    public $currentGroup;

    public null|int|float $score = null;
    public bool $hasFeedback = false;
    public string $feedback = '';

    protected bool $skipBooted = false;

    /* Lifecycle methods */
    protected function getListeners(): array
    {
        return ['accordion-update' => 'handlePanelActivity'];
    }

    /* Event listener methods */
    /**
     * @param $panelData
     * @return void
     * @throws AssessmentException
     */
    public function handlePanelActivity($panelData): void
    {
        $panelName = str($panelData['key'])->camel()->append('Panel')->value();
        if (!property_exists($this, $panelName)) {
            throw new AssessmentException('Panel update for unknown panel property.');
        }

        $this->$panelName = $panelData['value'];
    }

    /* Computed properties */
    abstract public function getShowScoreSliderProperty(): bool;

    abstract public function getShowCoLearningScoreToggleProperty(): bool;

    /* Public accessible methods */
    abstract public function redirectBack();

    abstract public function finalAnswerReached(): bool;

    abstract public function loadQuestion(int $position): bool|array;
    abstract public function next(): bool;

    abstract public function previous(): bool;

    public function coLearningRatings()
    {
        return $this->studentRatings()
            ->each(function ($answerRating) {
                $answerRating->user ??= \tcCore\User::getDeletedNewUser();
                $rating = '-';
                if (!is_null($answerRating->rating)) {
                    $rating = $this->currentQuestion->decimal_score
                        ? (float)$answerRating->rating
                        : (int)$answerRating->rating;
                }
                $answerRating->displayRating = $rating;
            });
    }

    /* Private methods */
    abstract protected function setData(): void;
    abstract protected function getTestTakeData(): TestTake;
    abstract protected function getAnswers(): Collection;
    abstract protected function getQuestions(): Collection;

    abstract protected function start(): void;

    abstract protected function setTemplateVariables(): void;

    abstract protected function initializeNavigationProperties(): void;

    abstract protected function hydrateCurrentProperties(): void;

    abstract protected function currentAnswerCoLearningRatingsHasNoDiscrepancy(): bool;

    abstract protected function handleAnswerScore(): null|int|float;

    protected function getGroups(): Collection
    {
        return $this->testTakeData->test->testQuestions
            ->map(fn($testQuestion) => $testQuestion->question->isType('Group') ? $testQuestion->question : null)
            ->filter();
    }

    protected function handleGroupQuestion(): void
    {
        if (!$this->currentQuestion->belongs_to_groupquestion_id) {
            $this->currentGroup = null;
            return;
        }

        $this->currentGroup = $this->groups->where('id', $this->currentQuestion->belongs_to_groupquestion_id)->first();
    }

    protected function currentAnswerRatings(): Collection
    {
        return $this->currentAnswer->answerRatings;
    }

    protected function teacherRating(): ?AnswerRating
    {
        return $this->currentAnswer
            ->answerRatings
            ->where('type', AnswerRating::TYPE_TEACHER)
            ->first();
    }

    protected function systemRating(): ?AnswerRating
    {
        return $this->currentAnswer
            ->answerRatings
            ->where('type', AnswerRating::TYPE_SYSTEM)
            ->first();
    }

    protected function studentRatings(): Collection
    {
        return $this->currentAnswer
            ->answerRatings
            ->where('type', AnswerRating::TYPE_STUDENT);
    }

}