<?php

namespace tcCore\Observers;

use tcCore\Versionable;

class VersionableObserver
{
    protected static array $massUpdating = [];

    public function updating(Versionable $versionable): bool
    {
        if (!$versionable->needsDuplication()) {
            return true;
        }

        $versionable->handleDuplication();
        return false;
    }

    public static function setMassUpdating($identifier, $type): void
    {
        self::$massUpdating[] = ['id' => $identifier, 'type' => $type];
    }

    public static function isMassUpdating($identifier, $type): bool
    {
        return collect(self::$massUpdating)
            ->where('id', $identifier)
            ->where('type', $type)
            ->isNotEmpty();
    }

    public static function clearMassUpdating($identifier = null, $type = null): void
    {
        if ($identifier && $type) {
            $key = collect(self::$massUpdating)
                ->search(fn($entry) => $entry['id'] === $identifier && $entry['type'] === $type);
            if ($key !== false) {
                unset(self::$massUpdating[$key]);
            }
            return;
        }

        self::$massUpdating = [];
    }
}
