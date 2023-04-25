<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCModalComponent;

class TestStartCreateModal extends TCModalComponent
{
    public function mount()
    {
        if (Auth::user()->isValidExamCoordinator()) {
            abort(403);
        }
    }

    public function goToCreateTest()
    {
        $this->emitTo('teacher.test-create-modal', 'showModal');
    }
    public function goToUploadTest()
    {
        $path = BaseHelper::getLivewireOriginalPath(request()) ?? '';
        return redirect(route('teacher.upload-tests', ['referrer' => ['type' => 'laravel', 'page' => $path]]));
    }

    public static function modalMaxWidth(): string
    {
        return '4xl';
    }



    public function render()
    {
        return view('livewire.teacher.test-start-create-modal');
    }
}
