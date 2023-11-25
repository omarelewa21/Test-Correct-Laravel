<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Test;
use tcCore\TestAuthor;
use tcCore\User;

class OlympiadeArchiveService extends ContentSourceService
{
    public static int $order = 700;

    public static function getTranslation(): string
    {
        return __('general.Olympiade Archive');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getPublishScope(): string|array|null
    {
        return 'published_olympiade_archive';
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return 'SBON';
    }

    public static function getName(): string
    {
        return 'olympiade_archive';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return (new static)->itemBankFiltered(filters: [], sorting: [], forUser: $user)->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->allow_olympiade_archive;
    }

    public static function getCustomerCode(): array|string|null
    {
        return config('custom.olympiade_archive_school_customercode');
    }

    public static function addAuthorToTest(Test $test): bool
    {
        if (!auth()->check()) {
            return false;
        }

        if (!self::inSchool(Auth::user())) {
            return false;
        }
        if ($test->scope != static::getPublishScope()) {
            return false;
        }
        $test->testAuthors->each(function ($testAuthor) {
            $testAuthor->delete();
        });
        return TestAuthor::addOrRestoreAuthor($test, self::getSchoolAuthor()->getKey());
    }

    private static function inSchool(User $user): bool
    {
        return $user->schoolLocation?->customer_code === config('custom.olympiade_archive_school_customercode');
    }

    public static function getSchoolAuthor(): User|null
    {
        return User::where('username', config('custom.olympiade_archive_school_author'))->first();
    }

}
