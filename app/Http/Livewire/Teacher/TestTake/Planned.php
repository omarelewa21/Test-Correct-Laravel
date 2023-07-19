<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Invigilator;
use tcCore\Log;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;

class Planned extends TestTakeComponent
{
    public Collection $invigilatorUsers;

    public function mount(TestTakeModel $testTake): void
    {
        parent::mount($testTake);
        $this->setStudentData();
    }

    public function refresh(): void
    {
        $this->fillGridData();
        $this->setStudentData();
        $this->setInvigilators();
    }

    public function redirectToOverview()
    {
        return CakeRedirectHelper::redirectToCake('planned.my_tests');
    }

    public function removeParticipant($participantUuid): void
    {
        try {
            TestParticipant::whereUuid($participantUuid)->delete();
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    public function removeInvigilator($invigilatorUuid): void
    {
        try {
            Invigilator::whereUuid($invigilatorUuid)->delete();
        } catch (\Exception $e) {
            Log::error($e);
        }
        $this->fillGridData();
        $this->setInvigilators();
    }

    public function canStartTestTake(): bool
    {
        return $this->testTake->time_start->isToday();
    }

    public function startTake(): void
    {
        $warnings = collect([
            'browser_testing' => $this->testTake->allow_inbrowser_testing,
            'guest_accounts' => $this->testTake->guest_accounts,
            'participants_incomplete' => $this->participants->count() !== $this->activeParticipantUuids->count(),
        ])->filter();

        if ($warnings->isNotEmpty()) {
            $this->emit(
                'openModal',
                'teacher.test-take-warning-modal',
                ['testTake' => $this->testTakeUuid, 'warnings' => $warnings]
            );
            return;
        }

        $this->testTake->startTake();
        CakeRedirectHelper::redirectToCake('planned.surveillance');
    }

    public function breadcrumbTitle(): string
    {
        return __('header.Mijn ingeplande toetsen');
    }
}