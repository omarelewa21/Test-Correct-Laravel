<?php

namespace tcCore\Http\Livewire\Teacher\Cms;

use tcCore\UserSystemSetting;
use tcCore\Http\Enums\UserSystemSetting as UserSystemSettingEnum;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\UserFeatureSetting;

class ConfirmRelationQuestionUsageModal extends TCModalComponent
{
    public bool $dontShowAgain = false;

    public static function modalMaxWidthClass(): string
    {
        return 'max-w-2xl';
    }

    public function continue(): void
    {
        if ($this->dontShowAgain) {
            $this->registerDontShowAgain();
        }

        $this->closeModal();
    }

    private function registerDontShowAgain(): void
    {
        UserSystemSetting::setSetting(
            auth()->user(),
            UserSystemSettingEnum::CMS_ADD_RELATION_QUESTION_CONFIRMATION,
            true
        );
    }
}
