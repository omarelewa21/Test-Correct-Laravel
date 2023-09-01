<?php

namespace tcCore\Http\Helpers;

use tcCore\User;

class Settings
{
    public function canUseCmsWscWriteDownToggle(): bool
    {
        return (bool) auth()->user()->schoolLocation?->allow_cms_write_down_wsc_toggle;
    }

    public function canUsePlannedTestPage(?User $user = null): bool
    {
        $user ??= auth()->user();
        return (bool) $user->schoolLocation?->allow_new_test_take_detail_page;
    }

    public function canUseTakenTestPage(?User $user = null): bool
    {
        $user ??= auth()->user();
        return (bool) $user->schoolLocation?->allow_new_test_taken_pages;
    }
}