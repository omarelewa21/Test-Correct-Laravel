<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Test;
use tcCore\TestAuthor;
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
        return (new static())->itemBankFiltered(forUser: $user)->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->allow_formidable;
    }

    public static function getCustomerCode(): array|string|null
    {
        return config('custom.formidable_school_customercode');
    }

    public static function addAuthorToTest(Test $test): bool
    {
        if (!auth()->check()) {
            return false;
        }
        if (!self::inSchool(Auth::user())) {
            return false;
        }
        if ($test->scope != 'published_formidable') {
            return false;
        }
        $test->testAuthors->each(function ($testAuthor) {
            $testAuthor->delete();
        });
        return TestAuthor::addOrRestoreAuthor($test, self::getSchoolAuthor()->getKey());
    }

    private static function inSchool(User $user): bool
    {
        return $user->schoolLocation?->customer_code == config('custom.formidable_school_customercode');
    }

    public static function getSchoolAuthor(): User|null
    {
        return User::where('username', config('custom.formidable_school_author'))->first();
    }
}
