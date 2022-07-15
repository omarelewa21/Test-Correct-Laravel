<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Test;

class TestDuplicateTest extends Component
{

    public $uuid;
    public $variant;

    public function mount($uuid, $variant='icon-button')
    {
        $this->uuid = $uuid;
        $this->variant = $variant;
    }


    public function duplicateTest()
    {
        $test = Test::findByUuid($this->uuid);

        if ($test->canCopy(auth()->user())) {
            try {
                $newTest = $test->userDuplicate([], Auth::id());
            } catch (\Exception $e) {
                return 'Error duplication failed';
            }
            $this->dispatchBrowserEvent('notify', ['message' => __('general.duplication successful')]);

            redirect()->to(route('teacher.test-detail', ['uuid' => $newTest->uuid]));

            return __('general.duplication successful');
        }

        if ($test->canCopyFromSchool(auth()->user())) {
            $this->emitTo('teacher.copy-test-from-schoollocation-modal', 'showModal',  $test->uuid);
            return true;
        }
    }

    public function render()
    {
        return view('livewire.actions.test-duplicate-test');
    }
}
