<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

class FormidableService extends ContentSourceService
{
    public static int $order = 600;

    public static function getTranslation(): string
    {
        return __('general.Formidable');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'formidable';
    }

    public static function getPublishScope(): string|array|null
    {
        return 'published_formidable';
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return 'FD';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return Test::formidableItemBankFiltered()->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->allow_formidable;
    }
}
