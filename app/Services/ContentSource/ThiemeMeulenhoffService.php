<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use tcCore\BaseSubject;
use tcCore\Http\Enums\SchoolLocationFeatureSetting;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestAuthor;
use tcCore\User;

class ThiemeMeulenhoffService extends ContentSourceService
{
    public static int $order = 700;

    public static function getTranslation(): string
    {
        return __('general.ThiemeMeulenhoff');
    }

    public static function highlightTab(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'thieme_meulenhoff';
    }

    public static function getPublishScope(): string|array|null
    {
        return 'published_thieme_meulenhoff';
    }

    public static function getPublishAbbreviation(): string|array|null
    {
        return 'TM';
    }

    public static function getAllFeatureSettings(): Collection
    {
        return collect([
            SchoolLocationFeatureSetting::ALLOW_TM_BIOLOGY,
            SchoolLocationFeatureSetting::ALLOW_TM_GEOGRAPHY,
            SchoolLocationFeatureSetting::ALLOW_TM_DUTCH,
            SchoolLocationFeatureSetting::ALLOW_TM_ENGLISH,
            SchoolLocationFeatureSetting::ALLOW_TM_FRENCH,
        ]);
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return (new static())->itemBankFiltered(forUser: $user)->exists();
    }

    protected static function allowedForUser(User $user): bool
    {
        return self::featureSettingForUser($user)->isNotEmpty();
    }

    protected static function featureSettingForUser(User $user): Collection
    {
        return self::getAllFeatureSettings()->filter(function (SchoolLocationFeatureSetting $setting) use ($user) {
            $setting = $setting->value;
            return $user->schoolLocation->$setting;
        });
    }

    private static function getAllowedBaseSubjectIds(User $user): Collection
    {
        return self::featureSettingForUser($user)->map(function ($setting) {
            return match ($setting) {
                SchoolLocationFeatureSetting::ALLOW_TM_BIOLOGY   => BaseSubject::BIOLOGY,
                SchoolLocationFeatureSetting::ALLOW_TM_GEOGRAPHY => BaseSubject::GEOGRAPHY,
                SchoolLocationFeatureSetting::ALLOW_TM_DUTCH     => BaseSubject::DUTCH,
                SchoolLocationFeatureSetting::ALLOW_TM_ENGLISH   => BaseSubject::ENGLISH,
                SchoolLocationFeatureSetting::ALLOW_TM_FRENCH    => BaseSubject::FRENCH,
                default                                          => null
            };
        })->filter();
    }

    public static function getBuilderWithAllowedSubjectIds($user): Builder
    {
        $allowedBaseSubjects = self::getAllowedBaseSubjectIds($user);
        return Subject::select('id')->whereIn('base_subject_id', $allowedBaseSubjects);
    }

    public function itemBankFiltered(User $forUser, $filters = [], $sorting = []): \Illuminate\Database\Eloquent\Builder
    {
        return parent::itemBankFiltered($forUser, $filters, $sorting)
            ->whereIn(
                'subject_id',
                self::getBuilderWithAllowedSubjectIds($forUser)
            );
    }

    public static function getCustomerCode(): array|string|null
    {
        return config('custom.thieme_meulenhoff_school_customercode');
    }

    public static function addAuthorToTest(Test $test): bool
    {
        if (!auth()->check()) {
            return false;
        }
        if (!self::inSchool(auth()->user())) {
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
        return $user->schoolLocation?->customer_code == config('custom.thieme_meulenhoff_school_customercode');
    }

    public static function getSchoolAuthor(): User|null
    {
        return User::where('username', config('custom.thieme_meulenhoff_school_author'))->first();
    }

    public function wordListFiltered(User $forUser, $filters = [], $sorting = []): Builder
    {
        return parent::wordListFiltered($forUser, $filters, $sorting)
            ->whereIn(
                'subject_id',
                self::getBuilderWithAllowedSubjectIds($forUser)
            );
    }
}
