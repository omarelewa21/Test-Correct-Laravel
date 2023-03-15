<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class NationalItemBankService extends ContentSourceService
{
    public static int $order = 400;

    public static function getTranslation(): string
    {
        return __('general.Nationaal');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'national';
    }

    public static function getPublishScope(): string|array|null
    {
        return ['exam', 'ldt'];
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return ['EXAM', 'LDT'];
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