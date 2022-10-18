<?php

namespace tcCore\Http\Livewire\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Test;

class TestMakePdf extends TestAction
{
    public function mount($uuid, $variant = 'icon-button', $class = '')
    {
        parent::mount($uuid, $variant, $class);
    }

    public function handle()
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

    protected function getDisabledValue(): bool
    {
        return !$this->test->canEdit(Auth::user()) && !Auth::user()->isValidExamCoordinator();
    }
}
