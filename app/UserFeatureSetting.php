<?php

namespace tcCore;

use tcCore\Lib\Models\UserSettingModel;

class UserFeatureSetting extends UserSettingModel
{
    static protected function sessionKey(User $user): string
    {
        return sprintf('.user-%s-feature-settings', $user->uuid);
    }
}
