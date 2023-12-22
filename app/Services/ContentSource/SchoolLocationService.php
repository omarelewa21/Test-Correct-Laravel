<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class SchoolLocationService extends ContentSourceService
{
    public static int $order = 200;

    public static function getTranslation(): string
    {
        return __('general.School');
    }

    public static function getName(): string
    {
        return 'school_location';
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
        return true;
    }

    protected static function allowedForUser(User $user): bool
    {
        return true;
    }
    public  function itemBankFiltered(User $forUser, $filters = [], $sorting = []): \Illuminate\Database\Eloquent\Builder
    {
        return Test::filtered(
            $filters, $sorting
        )
            ->published();
    }
}