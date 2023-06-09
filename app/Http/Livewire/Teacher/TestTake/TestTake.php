<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use tcCore\Events\TestTakePresenceEvent;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithReturnHandling;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;

abstract class TestTake extends TCComponent
{
    use WithReturnHandling;

    public string $testTakeUuid;
    protected TestTakeModel $testTake;
    public Collection $participants;
    public Collection $activeParticipantUuids;

    public array $gridData = [];
    public bool $initialized = false;

    protected function getListeners(): array
    {
        return [
            TestTakePresenceEvent::channelHereSignature($this->testTake->uuid)    => 'initializingPresenceChannel',
            TestTakePresenceEvent::channelJoiningSignature($this->testTake->uuid) => 'joiningPresenceChannel',
            TestTakePresenceEvent::channelLeavingSignature($this->testTake->uuid) => 'leavingPresenceChannel',
        ];
    }

    public function mount(TestTakeModel $testTake)
    {
        $this->testTakeUuid = $testTake->uuid;
        $this->setTestTake($testTake);
        $this->fillGridData();
        $this->participants = collect();
        $this->activeParticipantUuids = collect();
    }

    public function hydrate()
    {
        $this->setTestTake();
    }

    public function render()
    {
        if ($this->initialized) {
            $this->setStudentData();
        }
        $template = class_basename(get_called_class());
        return view('livewire.teacher.test-take.' . $template)->layout('layouts.app-teacher');
    }

    abstract public function redirectToOverview();

    public function back()
    {
        return $this->redirectUsingReferrer();
    }

    private function setTestTake(TestTakeModel $testTake = null): void
    {
        $this->testTake = $testTake ?? TestTakeModel::whereUuid($this->testTakeUuid)->first();
    }

    public function initializingPresenceChannel($event)
    {
        $this->handlePresenceEventUpdate(collect($event)->where('student', true)->pluck('uuid'));
    }

    public function joiningPresenceChannel($event)
    {
        $this->handlePresenceEventUpdate($this->activeParticipantUuids->push($event['uuid']));
    }

    public function leavingPresenceChannel($event)
    {
        $this->handlePresenceEventUpdate(collect($event)->where('student', true)->pluck('uuid'));
    }

    public function removeParticipant(TestParticipant $participant)
    {
        try {
            $participant->delete();
        } catch (\Exception) {}
    }

    private function handlePresenceEventUpdate(Collection $presentUserUuids)
    {
        $this->initialized = true;
        $this->activeParticipantUuids = $presentUserUuids;
        $this->setStudentData();
    }
}
