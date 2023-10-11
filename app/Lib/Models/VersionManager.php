<?php

namespace tcCore\Lib\Models;

use tcCore\User;
use tcCore\Versionable;

class VersionManager
{
    /**
     * Get the Versionable model based on the given user
     * @param Versionable $versionable
     * @param User $user
     * @return Versionable
     */
    public static function getVersionable(Versionable $versionable, User $user): Versionable
    {
        $versionable = self::retrieveCorrectVersionForUser($versionable, $user);
        $versionable->setEditingAuthor($user);

        return $versionable;
    }

    private static function getExistingVersionForUser(Versionable $versionable, User $user): ?Versionable
    {
        $searchOriginal = !$versionable->isOriginal();
        return $versionable::where('user_id', $user->getKey())
            ->whereIn($versionable->getTable() . '.id', function ($query) use ($searchOriginal, $versionable) {
                $query->select($searchOriginal ? 'original_id' : 'versionable_id')
                    ->from('versions')
                    ->where($searchOriginal ? 'versionable_id' : 'original_id', $versionable->getKey())
                    ->where('versionable_type', $versionable::class);
            })
            ->first();
    }

    private static function getLatestNonOriginalVersion(Versionable $versionable): ?Versionable
    {
        return $versionable::whereIn($versionable->getTable() . '.id', function ($query) use ($versionable) {
            $query->selectRaw('MAX(versionable_id)')
                ->from('versions')
                ->where('original_id', $versionable->getKey())
                ->where('versionable_type', $versionable::class)
                ->whereRaw('versionable_id != original_id');
        })->first();
    }

    private static function retrieveCorrectVersionForUser(Versionable $versionable, User $user): Versionable
    {
        if ($versionable->user->is($user)) {
            return $versionable;
        }

        if ($existingVersion = self::getExistingVersionForUser($versionable, $user)) {
            return $existingVersion;
        }

        if ($latestVersion = self::getLatestNonOriginalVersion($versionable)) {
            return $latestVersion;
        }

        return $versionable;
    }
}