<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class UmbrellaOrganizationService extends ContentSourceService
{
    public static function getTranslation(): string
    {
        return __('general.Scholengemeenschap');
    }

    public static function highlightTab(): bool
    {
        return false;
    }

    public static function getTabName(): string
    {
        return 'umbrella';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return Test::sharedSectionsFiltered()->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->hasSharedSections() && !$user->isValidExamCoordinator();
    }
}