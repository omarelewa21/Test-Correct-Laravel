<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class OlympiadeService extends ContentSourceService
{
    public static function getTranslation(): string
    {
        return __('general.Olympiade');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getTabName(): string
    {
        return 'olympiade';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return Test::OlympiadeItemBankFiltered()->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->allow_olympiade;
    }
}