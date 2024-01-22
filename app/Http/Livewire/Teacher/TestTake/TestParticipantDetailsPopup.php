<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Facades\Gate;
use tcCore\Http\Livewire\TCComponent;
use tcCore\TestParticipant;

class TestParticipantDetailsPopup extends TCComponent
{
    public ?TestParticipant $testParticipant = null;

    public function render()
    {
        return view('livewire.teacher.test-take.test-participant-details-popup');
    }

    public function openPopup(TestParticipant $testParticipant): bool
    {
        Gate::authorize('isAllowedToViewTestTake',[$testParticipant->testTake]);

        $this->testParticipant = $testParticipant;
        $this->testParticipant->calculateStatistics();

        return true;
    }

    public function closePopup(): bool
    {
        $this->testParticipant = null;
        return true;
    }

}
