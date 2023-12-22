<?php

namespace tcCore\Services\ContentSource;

use tcCore\Test;
use tcCore\User;

abstract class ContentSourceService
{
    public static int $order;

    /**
     * Get the translation string for the name of the content source.
     *
     * @return string
     */
    abstract public static function getTranslation(): string;

    abstract public static function getName(): string;

    /**
     * Get wheter the content source is allowed and tests are available for the user
     *
     * @return string
     */
    public static final function isAvailableForUser(User $user): bool
    {
        return static::allowedForUser($user)
            && static::testsAvailableForUser($user);
    }

    /**
     * Get whether the tab for the content source in the question/test overviews should be highlighted.
     *
     * @return bool
     */
    public static function highlightTab(): bool
    {
        return false;
    }

    /**
     * Get the content source scope that is set after publishing the tests and test questions.
     *
     * Returns null when the ContentSource is not publishable.
     * @return string|null
     */
    abstract public static function getPublishScope(): string|array|null;

    /**
     * Get the content source abbreviation that is used to publish the tests and test questions.
     *
     * Returns null when the ContentSource is not publishable.
     * @return string|null
     */
    abstract public static function getPublishAbbreviation(): string|array|null;

    /**
     * Get whether there are tests from the content source available for the authenticated user
     *
     * @return bool
     */
    abstract protected static function testsAvailableForUser(User $user): bool;

    /**
     * Get whether the content source is allowed for the user.
     *
     * @return bool
     */
    abstract protected static function allowedForUser(User $user): bool;

    public static function getNotPublishScope(): string|null|array
    {
        if (static::getPublishScope() === null) return null;
        return 'not_' . static::getPublishScope();
    }

    public  function itemBankFiltered(User $forUser, $filters = [], $sorting = []): \Illuminate\Database\Eloquent\Builder
    {
        return (new Test())->contentSourceFiltered(
            static::getPublishScope(),
            static::getCustomerCode(),
            Test::query(),
            $forUser,
            $filters,
            $sorting,
        );
    }

    public static function getPrimaryScope(): string|null
    {
         if (is_array(static::getPublishScope())) {
            throw new \Exception('getPrimaryScope() MUST be implemented for content sources with multiple scopes');
         }
         return static::getPublishScope();
    }

    public static function getPublishPrimaryAbbreviation(): string|null
    {
        if (is_array(static::getPublishAbbreviation())) {
            throw new \Exception('getPrimaryAbbrivation() MUST be implemented for content sources with multiple scopes');
        }
        return static::getPublishAbbreviation();
    }

    public static function getCustomerCode(): array|string|null
    {
        return null;
    }

    public static function getSchoolAuthor(): User|null
    {
        return null;
    }
}
