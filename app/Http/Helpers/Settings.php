<?php

namespace tcCore\Http\Helpers;

use tcCore\User;
use tcCore\UserSystemSetting;

class Settings
{
    public function canUseCmsWscWriteDownToggle(?User $user = null): bool
    {
        return $this->canUseFeature('allow_cms_write_down_wsc_toggle',$user);
//        return ($user ?? auth()->user())->schoolLocation->allow_cms_write_down_wsc_toggle;
    }

    public function allowNewCoLearning(?User $user = null) : bool
    {
        return $this->canUseFeature('allow_new_co_learning',$user);
//        return ($user ?? auth()->user())->schoolLocation->allow_new_co_learning ?? false;
    }

    public function allowNewCoLearningTeacher(?User $user = null) : bool
    {
        return $this->canUseFeature('allow_new_co_learning_teacher',$user);
//        return ($user ?? auth()->user())->schoolLocation->allow_new_co_learning_teacher ?? false;
    }

    public function canUseFeature(String $feature, ?User $user = null): bool
    {
        $user ??= auth()->user();
        return (
            $user?->schoolLocation?->$feature
            || (
                $user
                && UserSystemSetting::getSetting(user:$user, title:$feature)
            )
        );
    }
}