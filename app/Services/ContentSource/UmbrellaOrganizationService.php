<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;
use Tests\ScenarioLoader;

class UmbrellaOrganizationService extends ContentSourceService
{
    public static int $order = 300;

    public static function getTranslation(): string
    {
        return __('general.Scholengemeenschap');
    }

    public static function getName(): string
    {
        return 'umbrella';
    }

    public static function getPublishScope(): string|array|null
    {
        return null;
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return null;
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return Test::sharedSectionsFiltered(filters:[], sorting:[], forUser:$user)->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->hasSharedSections() && !$user->isValidExamCoordinator();
    }
    public  function itemBankFiltered($filters = [], $sorting = [], User $forUser): \Illuminate\Database\Eloquent\Builder
    {
        return Test::sharedSectionsFiltered($filters, $sorting, forUser: $forUser)
            ->published();
    }
}
