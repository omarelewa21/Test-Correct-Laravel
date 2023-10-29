<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use tcCore\Http\Livewire\TCModalComponent;

class StartAssessmentAfterGradingModal extends TCModalComponent
{
    public string $continue;

    public function render()
    {
        return view('livewire.teacher.test-take.start-assessment-after-grading-modal');
    }
}
