<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Database\Eloquent\Builder;
use tcCore\Test;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;

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
    public  function itemBankFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return Test::filtered(
            $filters, $sorting
        )
            ->published();
    }

    public  function wordListFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return WordList::filtered($filters, $sorting)
            ->where('word_lists.school_location_id', $forUser->school_location_id);
    }

    protected static function wordListsAvailableForUser(User $user): bool
    {
        return true;
    }

    public  function wordFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return Word::filtered($filters, $sorting)
            ->where('words.school_location_id', $forUser->school_location_id)
            ->whereNull('word_id');
    }
}