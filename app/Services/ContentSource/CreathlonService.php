<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Support\Facades\Auth;
use tcCore\Test;
use tcCore\TestAuthor;
use tcCore\User;

class CreathlonService extends ContentSourceService
{
    public static int $order = 500;

    public static function getTranslation(): string
    {
        return __('general.Creathlon');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'creathlon';
    }

    public static function getPublishScope(): string|array|null
    {
        return 'published_creathlon';
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return 'PUBLS';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return (new static())->itemBankFiltered(forUser: $user)->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->allow_creathlon;
    }

    public static function getCustomerCode(): array|string|null
    {
        return config('custom.creathlon_school_customercode');
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

    private static function inSchool(User $user)
    {
        return $user->schoolLocation?->customer_code === config('custom.creathlon_school_customercode');
    }

    public static function getSchoolAuthor(): User|null
    {
        return User::where('username', config('custom.creathlon_school_author'))->first();
    }

    protected static function wordListsAvailableForUser(User $user): bool
    {
        return true;
    }
}

