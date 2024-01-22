<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
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
        if (Gate::denies('canUsePlannedTestPage')) {
            TestTakeModel::redirectToDetail($testTake->uuid);
        }
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
        return Gate::allows('isAllowedToViewTestTake',[$this->testTake]) && $this->testTake->time_start->isToday() && $this->hasStudentStartRequirement();
    }

    public function startTake(): void
    {
        if (!$this->canStartTestTake()) {
            $this->addError('cannot_start_take_before_start_date', [__('auth.something_went_wrong')]);
            return;
        }

        $warnings = $this->getWarningsAboutStartingTake();

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

    public function getWarningsAboutStartingTake(): Collection
    {
        return collect([
            'browser_testing' => $this->testTake->allow_inbrowser_testing,
            'guest_accounts' => $this->testTake->guest_accounts,
            'participants_incomplete' => $this->participants->count() !== $this->activeParticipantUuids->count(),
        ])->filter();
    }

    private function hasStudentStartRequirement(): bool
    {
        return $this->testTake->guest_accounts || $this->participants->isNotEmpty();
    }
}