<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\Services\ContentSource\UmbrellaOrganizationService;
use tcCore\Subject;
use tcCore\Test;
use tcCore\User;

class ContentSourceHelper
{
    const PUBLISHABLE_ABBREVIATIONS = [
        'EXAM',
        'LDT',
        'PUBLS',
        'SBON',
    ];
    const PUBLISHABLE_SCOPES = [
        'exam',
        'ldt',
        'published_creathlon',
        'published_olympiade',
    ];

    const AVAILABLE_SOURCES = [
        'umbrella'  => UmbrellaOrganizationService::class,
        'national'  => NationalItemBankService::class,
        'creathlon' => CreathlonService::class,
        'olympiade' => OlympiadeService::class,
    ];

    public static function allAllowedForUser(User $user)
    {
        return collect(self::AVAILABLE_SOURCES)
            ->filter(function ($source) use ($user) {
                return $source::isAvailableForUser($user);
            });
    }

    public static function scopeIsAllowedForUser(User $user, string|null $testScope): bool
    {
        if(static::scopeHasNotBeenSet($testScope)) {
            return true;
        }

        return ContentSourceHelper::allAllowedForUser($user)
            ->map(fn($item, $publisher) => 'published_' . $publisher)
            ->contains($testScope);
    }

    public static function canViewContent(User $user, string $contentSourceName): bool
    {
        switch ($contentSourceName) {
            case 'personal':
            case 'school_location':
                return true;
            case 'umbrella':
                return UmbrellaOrganizationService::isAvailableForUser($user);
            case 'national':
                return NationalItemBankService::isAvailableForUser($user);
            case 'creathlon':
                return CreathlonService::isAvailableForUser($user);
            case 'olympiade':
                return OlympiadeService::isAvailableForUser($user);
        }

        return false;
    }

    protected static function scopeHasNotBeenSet(string|null $scope)
    {
        return $scope === '' || $scope === null;
    }
}