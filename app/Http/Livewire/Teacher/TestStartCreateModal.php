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
        $this->showModal = false;
        $this->emitTo('teacher.test-create-modal', 'showModal');
    }
    public function goToUploadTest()
    {
        $this->showModal = false;
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options'  => [
                'page'        => '/file_management/testuploads',
                'page_action' => "Popup.load('/file_management/upload_test',800);",
            ],
        ]);

        redirect($controller->toCakeUrl($request));
    }

    public static function modalMaxWidth(): string
    {
        // 'sm'
        // 'md'
        // 'lg'
        // 'xl'
        // '2xl'
        // '3xl'
        // '4xl'
        // '5xl'
        // '6xl'
        // '7xl'
        return '4xl';
    }



    public function render()
    {
        return view('livewire.teacher.test-start-create-modal');
    }
}
