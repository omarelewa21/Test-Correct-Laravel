<?php

namespace tcCore\Http\Livewire\ContextMenu;

use Illuminate\Support\Facades\Auth;
use tcCore\TemporaryLogin;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeCard extends ContextMenuComponent
{
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

    public function openTestTakeDetail()
    {
        return TestTake::redirectToDetailPage($this->uuid);
    }

    public function archive()
    {
        TestTake::whereUuid($this->uuid)->firstOrFail()->archiveForUser(Auth::user());

        $this->dispatchBrowserEvent('notify', ['message' => 'gearchiveerd']);
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

        $this->openTestTakeDetail();
    }

    public function studentAnswersPdf()
    {
        $pageUrl = sprintf('test_takes/view/%s', $this->uuid);
        $action = sprintf('Popup.load("/test_takes/answers_preview/%s", 1000)', $this->uuid);

        $temporaryLogin = TemporaryLogin::createWithOptionsForUser(
            ['page', 'page_action'],
            [$pageUrl, $action ],
            auth()->user()
        );

        $this->redirect($temporaryLogin->createCakeUrl());
        return;
    }
    public function hasAnswerPdfOption():bool
    {
        return collect([TestTakeStatus::STATUS_TAKEN,TestTakeStatus::STATUS_DISCUSSING])->contains($this->testTakeStatusId);
    }
    public function hasSkipDiscussing():bool
    {
        return collect([TestTakeStatus::STATUS_TAKEN])->contains($this->testTakeStatusId);
    }
    public function hasArchiveOption():bool
    {
        return !$this->isArchived;
    }
}
