<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Helpers\CakeRedirectHelper;

class UploadTestSuccessModal extends ModalComponent
{
    public static function modalMaxWidthClass(): string
    {
        return 'max-w-xl';
    }

    public function close()
    {
        return CakeRedirectHelper::redirectToCake('tests.my_uploads');
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
}