<?php

namespace tcCore\Http\Livewire;

use LivewireUI\Modal\ModalComponent;

class OpenUserLogoutWarningModal extends ModalComponent
{
    public function render()
    {
        return view('livewire.open-user-logout-warning-modal');
    }

    public function userLogout()
    {
        auth()->logout();
        return redirect(route('auth.login'));
    }
}
