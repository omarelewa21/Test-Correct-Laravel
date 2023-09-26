<?php

namespace tcCore\Http\Livewire;

use tcCore\Message;
use tcCore\User;

class MessageCreateModal extends TCModalComponent
{
    public User $receiver;
    public string $subject = '';
    public string $message = '';

    protected array $rules = [
        'subject' => 'required',
        'message' => 'required'
    ];

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

    public function send()
    {
        $this->validate();

        $message = new Message();
        $message->fill([
            'subject' => $this->subject,
            'message' => $this->message,
            'to'      => [$this->receiver->id],
        ]);
        $message->user_id = auth()->id();
        $message->save();

        $this->dispatchBrowserEvent('notify', ['message' => __('message.Bericht verzonden')]);
        $this->closeModal();
    }
}
