<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\AnonymousComponent;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Http\Livewire\Teacher\TestTakeWarningModal;
use tcCore\Invigilator;
use tcCore\Log;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;
use tcCore\User;

class Planned extends TestTakeComponent
{
    public $dropdownData = [];
    public $selected = [];
    public Collection $invigilatorUsers;

    public $activeSelect = 'string-14371dbf-a00c-4373-9597-146ff91d0008';

    public function mount(TestTakeModel $testTake): void
    {
        parent::mount($testTake);
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
}