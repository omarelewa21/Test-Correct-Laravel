<?php

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;

trait CanLogout
{
    public function initializeCanLogout()
    {
        $this->listeners = array_merge($this->listeners, [
            'logout',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('auth.login');
    }
}