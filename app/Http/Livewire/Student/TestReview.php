<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\AnswerRating;

class TestReview extends Component
{
    /*Template properties*/
    public string $reviewableUntil = '';
    public $testName;

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

    /* Lifecycle methods */
    public function mount($testTakeUuid): void
    {
        $this->testTakeUuid = $testTakeUuid;

        $this->setReviewData();

        $this->startReview();

        $this->setTemplateVariables();
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

    /* Public accessible methods */
    public function redirectBack()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
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
                            'testParticipants.answers.answerRatings'
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
            ->values();
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

    private function loadQuestion(int $position): void
    {
        $index = $position - 1;

        $this->currentQuestion = $this->questions->get($index);
        $this->currentAnswer = $this->answers->where('question_id', $this->currentQuestion->id)->first();
        $this->questionPosition = $position;
    }

    private function initializeNavigationProperties(): void
    {
        $this->questionPosition = blank($this->questionPosition) ? 1 : $this->questionPosition;
    }

}
