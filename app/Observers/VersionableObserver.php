<?php

namespace tcCore\Observers;

use tcCore\Versionable;

class VersionableObserver
{
    public function updating(Versionable $versionable): bool
    {
        if (!$versionable->needsDuplication()) {
            return true;
        }

        $versionable->handleDuplication();
        return false;
    }
}
