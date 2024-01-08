<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use tcCore\Test;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;

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
    final public static function isAvailableForUser(User $user, string $context = 'test'): bool
    {
        return static::allowedForUser($user) && static::contextItemAvailableForUser($user, $context);
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

    protected static function wordListsAvailableForUser(User $user): bool
    {
        return (new static())->wordListFiltered($user)->exists();
    }

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

    public function itemBankFiltered(User $forUser, $filters = [], $sorting = []): \Illuminate\Database\Eloquent\Builder
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

    public function wordListFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return WordList::contentSourceFiltered(
            $forUser,
            Arr::wrap(static::getCustomerCode()),
            $filters,
            $sorting,
        );
    }

    public static function contextItemAvailableForUser(User $user, string $context): bool
    {
        return match ($context) {
            'test'             => static::testsAvailableForUser($user),
            'wordList', 'word' => static::wordListsAvailableForUser($user),
            default            => false
        };
    }


    public  function wordFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return Word::contentSourceFiltered(
            $forUser,
            Arr::wrap(static::getCustomerCode()),
            $filters,
            $sorting
        )
            ->whereNull('word_id');
    }
}
