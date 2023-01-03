<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Controllers\TemporaryLoginController;

class TestStartCreateModal extends ModalComponent
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
        $path = json_decode(request()->getContent())->fingerprint->path ?? '';
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
