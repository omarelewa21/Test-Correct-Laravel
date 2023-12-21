<?php

namespace tcCore\Http\Helpers;

use tcCore\RelationQuestion;
use tcCore\Subject;
use tcCore\User;
use tcCore\UserSystemSetting;

class Settings
{
    public function canUseCmsWscWriteDownToggle(?User $user = null): bool
    {
        return $this->canUseFeature('allow_cms_write_down_wsc_toggle', $user);
//        return ($user ?? auth()->user())->schoolLocation->allow_cms_write_down_wsc_toggle;
    }

    public function allowNewCoLearning(?User $user = null): bool
    {
        return $this->canUseFeature('allow_new_co_learning', $user);
//        return ($user ?? auth()->user())->schoolLocation->allow_new_co_learning ?? false;
    }

    public function allowNewCoLearningTeacher(?User $user = null): bool
    {
        return $this->canUseFeature('allow_new_co_learning_teacher', $user);
//        return ($user ?? auth()->user())->schoolLocation->allow_new_co_learning_teacher ?? false;
    }

    public function canUsePlannedTestPage(?User $user = null): bool
    {
        return $this->canUseFeature('allow_new_test_take_detail_page', $user);
//        return ($user ?? auth()->user())->schoolLocation?->allow_new_test_take_detail_page;
    }

    public function canUseTakenTestPage(?User $user = null): bool
    {
        return true;
        return $this->canUseFeature('allow_new_test_taken_pages', $user);
//        return ($user ?? auth()->user())->schoolLocation?->allow_new_test_taken_pages;
    }

    public function canUseFeature(string $feature, ?User $user = null): bool
    {
        $user ??= auth()->user();
        return (
            $user?->schoolLocation?->$feature
            || (
                $user
                && UserSystemSetting::getSetting(user: $user, title: $feature)
            )
        );
    }

    public function canUseRelationQuestion(?Subject $subject = null, ?User $user = null): bool
    {
        if (!$this->canUseFeature('allow_relation_question', $user)) {
            return false;
        }
        
        if (!$subject) {
            return false;
        }

        return RelationQuestion::hasCorrectBaseSubject($subject->baseSubject()->value('name'));
    }
}