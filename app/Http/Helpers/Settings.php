<?php

namespace tcCore\Http\Helpers;

use tcCore\User;
use tcCore\UserSystemSetting;

class Settings
{
    public function canUseCmsWscWriteDownToggle(?User $user = null): bool
    {
        return $this->canUseFeature('allow_cms_write_down_wsc_toggle',$user);
    }

    public function allowNewCoLearning(?User $user = null) : bool
    {
        return $this->canUseFeature('allow_new_co_learning',$user);
    }

    public function allowNewCoLearningTeacher(?User $user = null) : bool
    {
        return $this->canUseFeature('allow_new_co_learning_teacher',$user);
    }

    public function canUsePlannedTestPage(?User $user = null): bool
    {
        return $this->canUseFeature('allow_new_test_take_detail_page',$user);
    }

    public function canUseTakenTestPage(?User $user = null): bool
    {
        return $this->canUseFeature('allow_new_test_taken_pages',$user);
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