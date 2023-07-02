<?php

namespace tcCore\Http\Helpers;

class Settings
{
    public function canUseCmsWscWriteDownToggle(): bool
    {
        return auth()->user()->schoolLocation->allow_cms_write_down_wsc_toggle;
    }
}