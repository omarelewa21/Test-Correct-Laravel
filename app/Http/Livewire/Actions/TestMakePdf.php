<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Http\Request;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Test;

class TestMakePdf extends Component
{
    public $uuid;
    public $variant;
    public string $class;

    public function mount($uuid, $variant = 'icon-button', $class = '')
    {
        $this->uuid = $uuid;
        $this->variant = $variant;
        $this->class = $class;
    }

    public function render()
    {
        return view('livewire.actions.test-make-pdf');
    }

    public function getTemporaryLoginToPdfForTest()
    {
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => sprintf('/tests/view/%s', $this->uuid),
                'page_action' => sprintf("Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);", $this->uuid),
            ],
        ]);

        return $controller->toCakeUrl($request);
    }
}
