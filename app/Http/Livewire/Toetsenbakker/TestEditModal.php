<?php

namespace tcCore\Http\Livewire\Toetsenbakker;

use tcCore\Http\Traits\Modal\ToetsenbakkerTestActions;

class TestEditModal extends \tcCore\Http\Livewire\TestEditModal
{
    use ToetsenbakkerTestActions;

    public $fileManagement;

    public function mount($testUuid = null)
    {
        parent::mount($testUuid);
    }

    public function render()
    {
        return view('livewire.teacher.test-edit-modal');
    }
    protected function setProperties($testUuid)
    {
        parent::setProperties($testUuid);
        $this->fileManagement = $this->test->fileManagement;
    }
}