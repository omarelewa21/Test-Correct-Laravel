<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Test;

class TestDuplicateTest extends Component
{

    public $uuid;
    public $variant;
    public bool $showButton;
    public string $class;

    public function mount($uuid, $variant='icon-button', $class = '')
    {
        $this->showButton = !Test::findByUuid($this->uuid)->isNationalItem();
        $this->uuid = $uuid;
        $this->variant = $variant;
        $this->class = $class;
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

            redirect()->to(route('teacher.test-detail', ['uuid' => $newTest->uuid, 'referrer' => 'copy']));
            return __('general.duplication successful');
        }

        if ($test->canCopyFromSchool(auth()->user())) {
            $this->emit('openModal', 'teacher.copy-test-from-schoollocation-modal',  ['testUuid' => $test->uuid]);
            return true;
        }
    }

    public function render()
    {
        return view('livewire.actions.test-duplicate-test');
    }
}
