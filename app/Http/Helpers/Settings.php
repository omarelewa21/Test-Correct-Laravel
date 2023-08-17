<?php

namespace tcCore\Http\Helpers;

use tcCore\User;

class Settings
{
    public function canUseCmsWscWriteDownToggle(?User $user = null): bool
    {
        return ($user ?? auth()->user())->schoolLocation->allow_cms_write_down_wsc_toggle;
    }

    public function allowNewCoLearning(?User $user = null) : bool
    {
        return ($user ?? auth()->user())->schoolLocation->allow_new_co_learning ?? false;
    }
}