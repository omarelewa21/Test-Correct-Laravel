<?php

namespace tcCore\Http\Livewire;

use tcCore\User;

class MessageCreateModal extends TCModalComponent
{
    public User $receiver;

    public function mount(User $receiver)
    {
        $this->receiver = $receiver;
    }

    public function render()
    {
        return view('livewire.message-create-modal');
    }

    public static function modalMaxWidth(): string
    {
        return 'xl';
    }
}
