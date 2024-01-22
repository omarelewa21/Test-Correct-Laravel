<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Database\Eloquent\Builder;
use tcCore\Test;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;

class PersonalService extends ContentSourceService
{
    public static int $order = 100;

    public static function getTranslation(): string
    {
        return __('general.Persoonlijk');
    }

    public static function getName(): string
    {
        return 'personal';
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
        return !$user->isValidExamCoordinator();
    }

    public  function itemBankFiltered(User $forUser, $filters = [], $sorting = []): \Illuminate\Database\Eloquent\Builder
    {
        return Test::filtered(
            $filters, $sorting
        )
            ->where('tests.author_id', $forUser->getKey());
    }

    public  function wordListFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return WordList::filtered($filters, $sorting)
            ->where('word_lists.user_id', $forUser->getKey());
    }

    protected static function wordListsAvailableForUser(User $user): bool
    {
        return true;
    }

    public  function wordFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return Word::filtered($filters, $sorting)
            ->where('words.user_id', $forUser->getKey())
            ->whereNull('word_id');
    }
}
