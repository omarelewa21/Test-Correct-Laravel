<?php

namespace tcCore\Http\Livewire\Modal;

use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Traits\WithUpdatingHandling;

abstract class Preview extends TCModalComponent
{
    use WithUpdatingHandling;

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
