<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;

class PdfDownloadModal extends ModalComponent
{
    public $displayValueRequiredMessage = false;
    protected static array $maxWidths = [
        'w-modal'  => 'max-w-modal',
    ];

    public static function modalMaxWidth(): string
    {
        return 'w-modal';
    }

    public function mount()
    {
        //Test vs TestTake
    }

    public function submit()
    {
        //
    }

    public function render()
    {
        return view('livewire.teacher.pdf-download-modal');
    }

    public function close()
    {
        $this->closeModal();
    }
}
