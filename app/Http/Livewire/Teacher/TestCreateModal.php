<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;

class TestCreateModal extends Component
{
    public $showModal = true;
    public $modalId = 'test-create-modal';

    protected $listeners = [
        'showModal'
    ];

    public function showModal()
    {
        $this->showModal = ! $this->showModal;
    }

    public function hideModal()
    {
        $this->showModal = false;
    }

    public function goToCreateTest()
    {
        $this->showModal = false;
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options'  => [
                'page' => '/',
                'page_action' => "Loading.show();Popup.load('/file_management/upload_test',800);"
            ],
        ]);

        $this->redirectTo($controller->toCakeUrl($request));
    }
    public function goToUploadTest()
    {
        $this->showModal = false;
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options'  => [
                'page' => '/',
                'page_action' => "Loading.show();Popup.load('/file_management/upload_test',800);"
            ],
        ]);

        redirect($controller->toCakeUrl($request));
    }

    public function render()
    {
        return view('livewire.teacher.test-create-modal');
    }
}
