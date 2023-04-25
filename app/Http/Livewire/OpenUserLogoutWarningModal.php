<?php

namespace tcCore\Http\Livewire;

class OpenUserLogoutWarningModal extends TCModalComponent
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
