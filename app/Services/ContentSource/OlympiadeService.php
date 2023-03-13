<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class OlympiadeService extends ContentSourceService
{
    public static int $order = 600;

    public static function getTranslation(): string
    {
        return __('general.Olympiade');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getPublishScope(): string|array|null
    {
        return 'published_olympiade';
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return 'SBON';
    }

    public static function getName(): string
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