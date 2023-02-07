<?php

namespace tcCore;

use tcCore\Lib\Models\UserSettingModel;

class UserSystemSetting extends UserSettingModel
{
    static protected function sessionKey(User $user): string
    {
        return sprintf('_user-%s-system-settings', $user->uuid);
    }
}
