<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use tcCore\Services\ContentSource\ContentSourceService;
use tcCore\User;

class ContentSourceHelper
{

    public static function allAllowedForUser(User $user, string $context = 'test')
    {
        return static::getAvailableSourcesInCorrectOrder()
            ->filter(fn($source) => $source::isAvailableForUser($user, $context));
    }

    public static function scopeIsAllowedForUser(User $user, string $testScope): bool
    {
        return ContentSourceHelper::allAllowedForUser($user)
            ->map(fn($item, $publisher) => 'published_' . $publisher)
            ->contains($testScope);
    }

    public static function canViewContent(User $user, string $contentSourceName): bool
    {
        if ($contentSourceService = collect(self::getAvailableSourcesInCorrectOrder())->get($contentSourceName, false)) {
            return $contentSourceService::isAvailableForUser($user);
        }

        return false;
    }

    public static function getPublishableScopes(): Collection
    {
        return static::getAvailableSourcesInCorrectOrder()
            ->map(fn($source) => $source::getPublishScope())
            ->flatten()
            ->filter()
            ->values();
    }

    public static function getPublishableAbbreviations(): Collection
    {
        return static::getAvailableSourcesInCorrectOrder()
            ->map(fn($source) => $source::getPublishAbbreviation())
            ->flatten()
            ->filter()
            ->values();
    }

    private static function getAvailableSourcesInCorrectOrder()
    {
        return collect(self::getAllAvailableContentSourceServices())
            ->sortBy(fn($source) => $source::$order);
    }

    private static function getAllAvailableContentSourceServices()
    {
        return collect(Storage::disk('content_source')->files())
            ->mapWithKeys(function ($file) {
                $class = sprintf('tcCore\Services\ContentSource\\%s', str_replace('.php', '', $file));
                if (!class_exists($class) || $class === ContentSourceService::class) {
                    return [];
                }
                return [$class::getName() => $class];
            });
    }
}