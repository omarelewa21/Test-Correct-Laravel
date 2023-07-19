<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Redirector;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;
use tcCore\TestTakeStatus;

class Taken extends TestTakeComponent
{
    public array $takenTestData = [];
    public int $testTakeStatusId;

    public bool $showWaitingRoom = false;
    public bool $showStudentNames = true;
    public bool $reviewActive = false;

    /* Lifecycle methods */
    public function mount(TestTakeModel $testTake): void
    {
        parent::mount($testTake);
        $this->createSystemRatingsWhenNecessary();
        $this->testTakeStatusId = $this->testTake->test_take_status_id;
        $this->setTakenTestData();
        $this->setStudentData();

        $this->showWaitingRoom = $this->testTakeStatusId === TestTakeStatus::STATUS_TAKEN;
        $this->reviewActive = $this->testTake->review_active;
    }

    public function updatedReviewActive(bool $value): void
    {
        TestTakeModel::whereUuid($this->testTakeUuid)->update(['review_active' => $value]);
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
            TestTakeStatus::STATUS_TAKEN     => [
                'CO-Learning' => 'cta',
                'Assessment'  => 'primary'
            ],
            TestTakeStatus::STATUS_DISCUSSED => [
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
            return redirect()->route('teacher.co-learning', $this->testTakeUuid);
        }

        return $this->showWaitingRoom = true;
    }

    public function startAssessment(): Redirector|RedirectResponse
    {
        return redirect()->route('teacher.assessment', $this->testTakeUuid);
    }

    /* Protected methods */
    protected function setTakenTestData(): void
    {
        $questionsOfTest = $this->testTake->test->getFlatQuestionList();

        $this->takenTestData = [
            'questionCount'      => $questionsOfTest->count(),
            'discussedQuestions' => $this->discussedQuestions($questionsOfTest),
            'assessedQuestions'  => $this->assessedQuestions(),
            'questionsToAssess'  => $this->questionsToAssess(),
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
        return AnswerRating::where('test_take_id', $this->testTake->id)
            ->where('type', '!=', AnswerRating::TYPE_STUDENT)
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
}