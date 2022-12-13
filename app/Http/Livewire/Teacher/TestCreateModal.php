<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;

class TestCreateModal extends \tcCore\Http\Livewire\TestCreateModal
{
    public function mount()
    {
        if (Auth::user()->isValidExamCoordinator()) {
            abort(403);
        }
        parent::mount();
    }

}