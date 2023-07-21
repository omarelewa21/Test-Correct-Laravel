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

    public Collection $participantResults;

    /* Lifecycle methods */
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
        $this->testTake->loadMissing([
            'testParticipants',
            'testParticipants.user:id,name,name_first,name_suffix,uuid',
            'testParticipants.answers',
            'testParticipants.answers.answerRatings',
        ]);

        $this->participantResults = $this->testTake
            ->testParticipants
            ->each(function ($participant) {
                $participant->user->setAppends([]);
                $participant->name = html_entity_decode($participant->user->name_full);
                $participant->score = $this->getScoreForParticipant($participant);
                $participant->discrepancies = $this->getDiscrepanciesForParticipant($participant);
                $participant->rated = $this->getRatedQuestionsForParticipant($participant);
            });
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
            $rating = $answer->answerRatings->first(function ($rating) {
                if ($rating->type === AnswerRating::TYPE_TEACHER) {
                    return $rating;
                };
                if ($rating->type === AnswerRating::TYPE_SYSTEM) {
                    return $rating;
                }
                return $rating;
            });
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
}