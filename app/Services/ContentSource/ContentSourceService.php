<?php

namespace tcCore\Services\ContentSource;

use tcCore\User;

abstract class ContentSourceService
{
    /**
     * Get the translation string for the name of the content source.
     *
     * @return string
     */
    abstract public static function getTranslation(): string;

    abstract public static function getTabName(): string;

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

}