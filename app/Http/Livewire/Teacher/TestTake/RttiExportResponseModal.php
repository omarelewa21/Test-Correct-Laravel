<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use tcCore\Http\Livewire\TCModalComponent;
use tcCore\RttiExportLog;

class RttiExportResponseModal extends TCModalComponent
{
    public RttiExportLog $rttiExportLog;

    public function render()
    {
        return view('livewire.teacher.test-take.rtti-export-response-modal');
    }
}
