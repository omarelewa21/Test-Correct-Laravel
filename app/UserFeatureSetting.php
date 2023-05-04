<?php

namespace tcCore;

use tcCore\Lib\Models\UserSettingModel;

class UserFeatureSetting extends UserSettingModel
{
    protected static function sessionKey(User $user): string
    {
        return sprintf('_user-%s-feature-settings', $user->uuid);
    }
}
