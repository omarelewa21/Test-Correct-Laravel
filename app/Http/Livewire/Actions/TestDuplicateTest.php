<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Test;

class TestDuplicateTest extends TestAction
{
    public bool $showButton;

    public function mount($uuid, $variant='icon-button', $class = '')
    {
        parent::mount($uuid, $variant, $class);
        $this->showButton = !Test::findByUuid($this->uuid)->isNationalItem();
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
}
