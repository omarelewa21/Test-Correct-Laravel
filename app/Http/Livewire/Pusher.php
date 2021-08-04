<?php

namespace tcCore\Http\Livewire;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Livewire\Component;
use tcCore\Events\RemoveFraudDetectionNotification;

class Pusher extends Component implements ShouldBroadcast
{

    protected $listeners = ['echo:my-channel,.my-event' => 'hansactie'];

    public function render()
    {
        return view('livewire.pusher')->layout('layouts.base');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('my-channel');
    }

    public function doeiets() {
        event(new RemoveFraudDetectionNotification('hello world'));
    }
    public function hansactie()
    {
        dd('hans!');
    }
}
