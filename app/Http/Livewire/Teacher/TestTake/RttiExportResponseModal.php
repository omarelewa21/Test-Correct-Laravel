<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use tcCore\Http\Livewire\TCModalComponent;
use tcCore\RttiExportLog;
use tcCore\Services\RttiExportService;

class RttiExportResponseModal extends TCModalComponent
{
    public ?RttiExportLog $rttiExportLog = null;
    public \tcCore\TestTake $testTake;

    public function mount(\tcCore\TestTake $testTake)
    {
        $this->testTake = $testTake;
    }

    public function createExport(): void
    {
        $this->rttiExportLog = (new RttiExportService($this->testTake))->createExport();
        if ($this->rttiExportLog->has_errors) {
            return;
        }

        $this->skipRender();
        $this->closeModal();
        $this->dispatchBrowserEvent('notify', ['message' => __('test-take.Toets met succes naar RTTI verzonden')]);
    }

    public function render()
    {
        return view('livewire.teacher.test-take.rtti-export-response-modal');
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

    public static function closeModalOnEscape(): bool
    {
        return false;
    }
}
