<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class CreathlonService extends ContentSourceService
{
    public static int $order = 500;

    public static function getTranslation(): string
    {
        return __('general.Creathlon');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'creathlon';
    }

    public static function getPublishScope(): string|array|null
    {
        return 'published_creathlon';
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return 'PUBLS';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return Test::creathlonItemBankFiltered()->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->allow_creathlon;
    }
}