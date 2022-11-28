<?php
namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use tcCore\Mail\PasswordChanged;
use tcCore\Mail\PasswordChangedSelf;

class PreviewMailController
{
    public function passwordChanged() {
        $user = Auth::user();
        return new PasswordChanged($user);
    }

    public function PasswordChangedSelf() {
        $user = Auth::user();
        return new PasswordChangedSelf($user);
    }
}