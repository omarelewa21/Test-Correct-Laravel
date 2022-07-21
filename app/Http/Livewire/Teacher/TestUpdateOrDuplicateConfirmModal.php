<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Traits\TestActions;
use tcCore\Test;

class TestUpdateOrDuplicateConfirmModal extends ModalComponent
{
    use TestActions;

    public $value = null;

    public $request = [];

    public $displayValueRequiredMessage = false;

    public function mount($request, $testUuid)
    {
        $this->request = $request;
        $this->testUuid = $testUuid;
    }

    public function updated()
    {
        $this->displayValueRequiredMessage = false;
    }

    public function submit()
    {
        if (!$this->value) {
            $this->displayValueRequiredMessage = true;
            return true;
        }
        $test = Test::whereUuid($this->testUuid)->firstOrFail();
        if (!$test->canEdit(Auth()->user())) {
            abort(403);
        }

        if ($this->value === 'update') {
            $this->update($test);
        }

        if ($this->value === 'duplicate') {
            $this->duplicate($test);
        }
    }

    private function duplicate(Test $test)
    {
        $newTest = $test->userDuplicate($this->request, auth()->id());
        $newTest->save();
        $this->forceClose()->closeModal();
        $this->emit('testSettingsUpdated', $this->request);

    }

    private function update(Test $test)
    {
        $test->fill($this->request);
        $test->save();
        $this->forceClose()->closeModal();
        $this->emit('testSettingsUpdated', $this->request);
    }

    public function render()
    {
        return view('livewire.teacher.test-update-or-duplicate-confirm-modal');
    }
}
