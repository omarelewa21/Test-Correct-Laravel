<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;
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

    public function __construct($testTake, $schoolClasses)
    {
        $this->testTake = $testTake;
        $this->author = $testTake->user->getFullNameWithAbbreviatedFirstName();
        $this->schoolClasses = $schoolClasses->map(fn($class) => $class->label)->join(', ');
        $this->archived = $testTake->archived;

        if ($this->withParticipantStats = $testTake->test_take_status_id === TestTakeStatus::STATUS_DISCUSSED) {
            $this->loadParticipantStats();
        }
    }

    public function render(): string
    {
        return 'components.grid.test-take-card';
    }

    private function loadParticipantStats()
    {
        $stats = $this->testTake->getParticipantTakenStats();
        $this->participantsTaken = $stats['taken'] ?? 0;
        $this->participantsNotTaken = $stats['notTaken'] ?? 0;
    }
}
