<?php

namespace tcCore\Http\Livewire\ContextMenu;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\CakeRedirectHelper;
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
        $testTake->testParticipants()->where('test_take_status_id', TestTakeStatus::STATUS_DISCUSSED)
            ->update(['test_take_status_id' => TestTakeStatus::STATUS_TAKEN]);
        $this->dispatchBrowserEvent('notify', ['message' => __('test_take.update_to_taken_toast')]);
        $this->emit('update-test-take-overview');
    }

    public function copyTestTakeLink()
    {
        $testTake = TestTake::whereUuid($this->uuid)->firstOrFail();
        $this->dispatchBrowserEvent(
            'copy-to-clipboard',
            [
                'message'       => $testTake->directLink,
                'notification'  => $testTake->isAssessmentType() ? __('teacher.assignment_clipboard_copied') : __('teacher.clipboard_copied')
            ]
        );
    }

    public function goToCoLearningPage()
    {
        $testTake = TestTake::whereUuid($this->uuid)->with('test', 'testParticipants')->firstOrFail();

        $pageAction = sprintf('TestTake.checkStartDiscussion("%s", %s, %s)', $this->uuid, 
            $testTake->test->hasOpenQuestion() ? 'false' : 'true', $testTake->hasNonActiveParticipant() ? 'true' : 'false'
        );

        return $this->openTestTakeDetail($this->uuid, $pageAction);
    }

    public function goToScheduleMakeUpPage()
    {
        return CakeRedirectHelper::redirectToCake('taken.schedule_makeup', $this->uuid);
    }

    public function hasGrantedPreviewAccess(): bool
    {
        return $this->uuid
            ? TestTake::whereUuid($this->uuid)->firstOrFail()->isAllowedToReviewResultsByParticipants()
            : false;
    }

    public function openAllowPreviewInNormPage()
    {
        return $this->openTestTakeDetail(
            $this->uuid,
            sprintf("Popup.load('/test_takes/update_show_results/%s', 420)", $this->uuid)
        );
    }

    public function closePreviewAccess()
    {
        return TestTake::whereUuid($this->uuid)->update(['show_results' => null]);
    }
}
