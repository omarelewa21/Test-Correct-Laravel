<?php

namespace tcCore\Http\Livewire\Teacher;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Livewire;
use LivewireUI\Modal\ModalComponent;
use tcCore\Test;

class TestDeleteModal extends ModalComponent
{
    public string $uuid;

    public function render()
    {
        return view('livewire.teacher.test-delete-modal');
    }

    public function mount($testUuid)
    {
        $this->uuid = $testUuid;
    }

    /**
     * @throws Exception
     */
    public function deleteTest()
    {
        $test = Test::whereUuid($this->uuid)->first();

        if (!$test->canDelete(Auth::user())) {
            return false;
        }

        $test->delete();

        $this->forceClose()->closeModal();

        if (Str::of(Livewire::originalUrl())->contains(route('teacher.tests', false, false))) {
            $this->emit('test-deleted');
            $this->dispatchBrowserEvent('notify', ['message' => __('teacher.Test is verwijderd')]);
            return true;
        }

        $this->redirect(route('teacher.tests', ['referrerAction' => 'test_deleted']));
        return true;
    }
}
