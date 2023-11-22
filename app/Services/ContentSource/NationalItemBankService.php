<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Test;
use tcCore\TestAuthor;
use tcCore\User;

class NationalItemBankService extends ContentSourceService
{
    public static int $order = 400;

    public static function getTranslation(): string
    {
        return __('general.Nationaal');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'national';
    }

    public static function getPublishScope(): string|array|null
    {
        return ['exam', 'ldt'];
    }

    public static function getNotPublishScope(): string|array|null
    {
        return ['not_exam', 'not_ldt'];
    }

    public static function getPrimaryScope(): string|null
    {
        return 'ldt';
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return ['EXAM', 'LDT'];
    }

    public static function getPublishPrimaryAbbreviation(): string|null
    {
        return 'LDT';
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return (new self)->itemBankFiltered( forUser: $user, filters: [], sorting: [])->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return $user->schoolLocation->show_national_item_bank;
    }

    public static function getCustomerCode(): array|string|null
    {
        return [
            config('custom.national_item_bank_school_customercode'),
            config('custom.examschool_customercode'),
            'CITO-TOETSENOPMAAT',
        ];
    }

    public static function addAuthorToTest(Test $test): bool
    {
        if (!auth()->check()) {
            return false;
        }
        if (!self::inSchool(Auth::user())) {
            return false;
        }
        if ($test->scope != 'ldt') {
            return false;
        }
        $test->testAuthors->each(function ($testAuthor) {
            $testAuthor->delete();
        });
        return TestAuthor::addOrRestoreAuthor($test, self::getSchoolAuthor()->getKey());
    }

    private static function inSchool(User $user): bool
    {
        return $user->schoolLocation?->customer_code === config('custom.national_item_bank_school_customercode');
    }

    public static function getSchoolAuthor(): User|null
    {
        return User::where('username', config('custom.national_item_bank_school_author'))->first();
    }

}

