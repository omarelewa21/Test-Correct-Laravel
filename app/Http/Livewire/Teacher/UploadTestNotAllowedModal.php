<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\TCModalComponent;

class UploadTestNotAllowedModal extends TCModalComponent
{
    public static function modalMaxWidthClass(): string
    {
        return 'max-w-xl';
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

    public static function closeModalOnEscape(): bool
    {
        return false;
    }

    public static function closeModalOnEscapeIsForceful(): bool
    {
        return false;
    }

    public function close()
    {
        return CakeRedirectHelper::redirectToCake('tests.my_uploads');
    }
}