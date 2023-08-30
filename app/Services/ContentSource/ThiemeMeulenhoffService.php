<?php

namespace tcCore\Services\ContentSource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use tcCore\BaseSubject;
use tcCore\Subject;
use tcCore\Test;
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

    public static function getAllFeatureSettings()
    {
        return collect([
            'allow_tm_biology',
            'allow_tm_geography',
            'allow_tm_dutch',
            'allow_tm_english',
            'allow_tm_french',
        ]);
    }

    protected static function testsAvailableForUser(User $user): bool
    {
        return Test::thiemeMeulenhoffItemBankFiltered()->exists();
    }



    protected static function allowedForUser(User $user): bool
    {
        return self::featureSettingForUser($user)->isNotEmpty();
    }

    protected static function featureSettingForUser(User $user): Collection
    {
        return self::getAllFeatureSettings()->filter(function ($setting) use ($user) {
            return $user->schoolLocation->$setting;
        });
    }

    private static function getAllowedBaseSubjectIds(User $user): Collection
    {
        return self::featureSettingForUser($user)->map(function ($setting) {
            return match ($setting) {
                'allow_tm_biology'   => BaseSubject::BIOLOGY,
                'allow_tm_geography' => BaseSubject::GEOGRAPHY,
                'allow_tm_dutch'     => BaseSubject::DUTCH,
                'allow_tm_english'   => BaseSubject::ENGLISH,
                'allow_tm_french'    => BaseSubject::FRENCH,
                default              => null
            };
        })->filter();
    }

    public static function getBuilderWithAllowedSubjectIds($user): Builder
    {
        $allowedBaseSubjects = self::getAllowedBaseSubjectIds($user);
        return Subject::select('id')->whereIn('base_subject_id', $allowedBaseSubjects);
    }
}