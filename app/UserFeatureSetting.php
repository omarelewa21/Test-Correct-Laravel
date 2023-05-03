<?php

namespace tcCore;

use tcCore\Lib\Models\UserSettingModel;

class UserFeatureSetting extends UserSettingModel
{
    protected static function sessionKey(User $user): string
    {
        return sprintf('.user-%s-feature-settings', $user->uuid);
    }
}
