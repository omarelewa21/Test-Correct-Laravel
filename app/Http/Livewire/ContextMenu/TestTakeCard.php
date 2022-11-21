<?php

namespace tcCore\Http\Livewire\ContextMenu;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Traits\WithTestTakeInteractions;
use tcCore\TemporaryLogin;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeCard extends ContextMenuComponent
{
    use WithTestTakeInteractions;

    const TAKEN_TAB = 'taken';
    public $uuid = null;
    public $testTakeStatusId;
    public $isArchived = false;

    public function setContextValues($uuid, $contextData): bool
    {
        $this->uuid = $uuid;

        $take = TestTake::whereUuid($uuid)->get(['test_take_status_id'])->first();
        $this->testTakeStatusId = $take->test_take_status_id;
        $this->isArchived = $take->archived;

        return true;
    }

    public function openTestTake()
    {
        return $this->openTestTakeDetail($this->uuid);
    }

    public function archive()
    {
        TestTake::whereUuid($this->uuid)->firstOrFail()->archiveForUser(Auth::user());

        $this->dispatchBrowserEvent('notify', ['message' => __('test-take.Gearchiveerd')]);
        $this->dispatchBrowserEvent($this->uuid . '-archived');
    }

    public function unarchive()
    {
        TestTake::whereUuid($this->uuid)->firstOrFail()->unArchiveForUser(Auth::user());

        $this->dispatchBrowserEvent('notify', ['message' => __('test-take.Gedearchiveerd')]);
        $this->dispatchBrowserEvent($this->uuid . '-unarchived');
    }

    public function skipDiscussing()
    {
        $testTake = TestTake::whereUuid($this->uuid)->firstOrFail();

        /* I think we have to do it the stupid way, because it relies on boot methods being called between status changes*/
        $testTake->fill(['test_take_status_id' => TestTakeStatus::STATUS_DISCUSSING]);
        $testTake->save();

        $testTake->refresh();

        $testTake->fill(['test_take_status_id' => TestTakeStatus::STATUS_DISCUSSED]);
        $testTake->save();

        $this->openTestTake();
    }

    public function studentAnswersPdf()
    {
        $this->emit('openModal','teacher.pdf-download-modal', ['uuid' => $this->uuid, 'testTake' => true]);
    }

    public function hasAnswerPdfOption(): bool
    {
        return collect(TestTakeStatus::STATUS_DISCUSSED)->contains($this->testTakeStatusId);
    }

    public function hasSkipDiscussing(): bool
    {
        return collect([TestTakeStatus::STATUS_TAKEN])->contains($this->testTakeStatusId);
    }

    public function hasArchiveOption(): bool
    {
        return !$this->isArchived;
    }

    public function hasUnarchiveOption(): bool
    {
        return $this->isArchived;
    }

    public function updateStatusToTaken()
    {
        $testTake = TestTake::whereUuid($this->uuid)->firstOrFail();
        $testTake->updateToTaken();
        $this->dispatchBrowserEvent('notify', ['message' => __('test-take.Gedearchiveerd')]);
        
        redirect(route('teacher.test-takes', self::TAKEN_TAB));
    }
}
