<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;

class TestUpdateOrDuplicateConfirmModal extends ModalComponent
{
    public $value;

    public function render()
    {
        return view('livewire.teacher.test-update-or-duplicate-confirm-modal');
    }
}
