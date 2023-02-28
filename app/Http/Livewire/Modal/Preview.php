<?php

namespace tcCore\Http\Livewire\Modal;

use LivewireUI\Modal\ModalComponent;

abstract class Preview extends ModalComponent
{
    public ?string $title = null;

    protected static array $maxWidths = [
        'full' => 'modal-full-screen',
    ];

    /**
     * Preview modal layout file:
     * components/partials/modal/preview.blade.php
     */
    abstract public function render();

    /*
     * Modal settings
     */
    public static function modalMaxWidth(): string
    {
        return 'full';
    }

    public static function destroyOnClose(): bool
    {
        return false;
    }
}
