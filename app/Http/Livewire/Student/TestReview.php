<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;

class TestReview extends Component
{
    /*Template properties*/
    public string $reviewableUntil = '';
    public string $testName;
    public bool $questionPanel = true;
    public bool $answerPanel = true;
    public bool $answerModelPanel = true;
    public bool $groupPanel = true;

    /*Query string properties*/
    protected $queryString = ['questionPosition' => ['except' => '', 'as' => 'question']];
    public string $questionPosition = '';

    /* Navigation properties */


    /* Data properties filled from cache */
    protected $testTakeData;
    protected $answers;
    protected $groups;
    protected $questions;

    /* Context properties */
    public $testTakeUuid;
    public $currentAnswer;
    public $currentQuestion;
    public $currentGroup;
    public $score = 1;
    public $hasFeedback = false;

    protected bool $skipBooted = false;

    /* Lifecycle methods */
    public function mount($testTakeUuid): void
    {
        $this->testTakeUuid = $testTakeUuid;

        $this->setReviewData();

        $this->startReview();

        $this->setTemplateVariables();

        $this->skipBooted = true;
    }

    public function booted(): void
    {
        if ($this->skipBooted) {
            return;
        }

        $this->setReviewData();
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

    public function getShowAutomaticallyScoredToggleProperty(): bool
    {
        return true;
    }

    public function getShowCoLearningScoreToggleProperty(): bool
    {
        return true;
    }

    public function getCoLearningScoredValueProperty(): int|float
    {
        return 5;
    }

    public function getAutomaticallyScoredValueProperty(): int|float
    {
        return 5;
    }

    public function getNeedsQuestionSectionProperty(): bool
    {
        return true;
    }

    /* Public accessible methods */
    public function redirectBack()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function loadQuestion(int $position): void
    {
        $index = $position - 1;

        $this->currentQuestion = $this->questions->get($index);
        $this->currentAnswer = $this->answers->where('question_id', $this->currentQuestion->id)->first();
        $this->questionPosition = $position;
        $this->handleGroupQuestion();
    }

    public function currentAnswerCoLearningRatingsHasNoDiscrepancy(): bool
    {
        return false;
    }

    public function coLearningRatings()
    {
        return [];
    }

    public function finalAnswerReached(): bool
    {
        return false;
    }

    public function next()
    {
        $this->loadQuestion((int)$this->questionPosition + 1);
    }

    public function previous()
    {
        $this->loadQuestion((int)$this->questionPosition - 1);
    }

    /* Private methods */
    private function setReviewData(): void
    {
        $userId = auth()->id();

        $this->testTakeData = cache()
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

        $this->answers = $this->testTakeData->testParticipants
            ->flatMap(fn($participant) => $participant->answers->map(fn($answer) => $answer))
            ->sortBy(['order', 'test_participant_id'])
            ->values();

        $this->groups = $this->testTakeData->test->testQuestions
            ->map(fn($testQuestion) => $testQuestion->question->isType('Group') ? $testQuestion->question : null)
            ->filter();

        $this->questions = $this->testTakeData->test->testQuestions
            ->sortBy('order')
            ->flatMap(function ($testQuestion) {
                $testQuestion->question->loadRelated();
                if ($testQuestion->question->type === 'GroupQuestion') {
                    $groupQuestion = $testQuestion->question;
                    return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                        $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                        return $item->question;
                    });
                }
                return collect([$testQuestion->question]);
            })
            ->filter(fn($question) => $this->answers->pluck('question_id')->contains($question->id))
            ->values();

        $this->addGroupConnectorPropertyToAnswerIfNecessary();
    }

    private function startReview(): void
    {
        $this->initializeNavigationProperties();

        $this->loadQuestion($this->questionPosition);
    }

    private function setTemplateVariables(): void
    {
        $this->testName = $this->testTakeData->test->name;
        $this->reviewableUntil = $this->testTakeData->show_results->translatedFormat('j F \'y - H:i');
    }

    private function initializeNavigationProperties(): void
    {
        $this->questionPosition = blank($this->questionPosition) ? 1 : $this->questionPosition;
    }

    private function hydrateCurrentProperties(): void
    {
        $this->currentQuestion = $this->questions->get((int)$this->questionPosition - 1);
    }

    private function handleGroupQuestion(): void
    {
        if (!$this->currentQuestion->belongs_to_groupquestion_id) {
            $this->currentGroup = null;
            return;
        }

        $this->currentGroup = $this->groups->where('id', $this->currentQuestion->belongs_to_groupquestion_id)->first();
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
                    ->stripy = true;
            });
    }

}
