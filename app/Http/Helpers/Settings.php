<?php

namespace tcCore\Http\Helpers;

use tcCore\User;

class Settings
{
    public function canUseCmsWscWriteDownToggle(): bool
    {
        return auth()->user()->schoolLocation->allow_cms_write_down_wsc_toggle;
    }

    public function canUseTestTakeDetailPage(?User $user = null): bool
    {
        $user ??= auth()->user();
        return $user->schoolLocation->allow_new_test_take_detail_page;
    }
}