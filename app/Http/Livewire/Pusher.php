<?php

namespace tcCore\Http\Livewire;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Livewire\Component;

class Pusher extends Component implements ShouldBroadcast
{
    public function render()
    {
        return view('livewire.pusher')->layout('layouts.base');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('my-channel');
    }
}
