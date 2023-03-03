<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class NationalItemBankService extends ContentSourceService
{
    public static function getTranslation(): string
    {
        return __('general.Nationaal');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getTabName(): string
    {
        return 'national';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return Test::NationalItemBankFiltered()->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->show_national_item_bank;
    }
}