<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use tcCore\Http\Helpers\AllowedAppType;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Mail\PasswordChanged;
use tcCore\Mail\PasswordChangedSelf;

trait UserNotificationForController
{
    public function sendPasswordChangedMail($user)
    {
        $mailable = new PasswordChanged($user);
        if (optional(Auth::user())->getKey() == $user->getKey()) {
            $mailable = new PasswordChangedSelf($user);
        }
        if(is_null(Auth::user())){
            $mailable = new PasswordChangedSelf($user);
        }
        Mail::to($user->username)->send($mailable);
    }

}