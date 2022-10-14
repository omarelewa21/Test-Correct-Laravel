<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Traits\Modal\TestActions;
use tcCore\Test;

class TestUpdateOrDuplicateConfirmModal extends ModalComponent
{
    use TestActions;

    public $value = null;

    public $request = [];

    public $displayValueRequiredMessage = false;
    protected static array $maxWidths = [
        'w-modal' => 'max-w-modal',
    ];

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

    public function close()
    {
        $this->closeModal();

    }

    private function duplicate(Test $test)
    {
        $testAttributes = $test->getAttributes();
        $newTestName = $this->request['name'];
        unset($testAttributes['name']);
        unset($this->request['name']);

        $newTest = $test->userDuplicate($testAttributes, Auth::id());

        $newTest->load(['testQuestions']);
        $newTest->fill($this->request)->save();

        if (!stristr($newTest->name, $newTestName)) {
            $newTest->name = $this->request['name'] = $newTestName;
            $newTest->save();
        }

        $this->forceClose()->closeModal();

        $this->redirect(route('teacher.test-detail', $newTest->uuid));
        return true;
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

    public static function modalMaxWidth(): string
    {
        return 'w-modal';
    }
}
