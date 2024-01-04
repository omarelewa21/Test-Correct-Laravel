<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;
use tcCore\RelationQuestion;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeCard extends Component
{
    public $testTake;
    public $author;
    public $schoolClasses;
    public bool $archived;

    public bool $withParticipantStats;
    public $participantsTaken;
    public $participantsNotTaken;
    public bool $disabledContextMenu = false;

    public function __construct($testTake, $schoolClasses)
    {
        $this->testTake = $testTake;
        $this->author = $testTake->user->getFullNameWithAbbreviatedFirstName();
        $this->schoolClasses = $schoolClasses->map(fn($class) => $class->label)->join(', ');
        $this->archived = $testTake->archived;

        $this->setDisabledContextMenu($testTake);

        if ($this->withParticipantStats = $testTake->hasStatusDiscussed()) {
            $this->loadParticipantStats();
        }
    }

    public function render(): string
    {
        return 'components.grid.test-take-card';
    }

    private function loadParticipantStats(): void
    {
        $stats = $this->testTake->getParticipantTakenStats();
        $this->participantsTaken = $stats['taken'] ?? 0;
        $this->participantsNotTaken = $stats['notTaken'] ?? 0;
    }

    private function setDisabledContextMenu(TestTake $testTake): void
    {
        if (settings()->canUseRelationQuestion(auth()->user())) {
            return;
        }

        $this->disabledContextMenu = $testTake->test->containsSpecificQuestionTypes(RelationQuestion::class);
    }
}
