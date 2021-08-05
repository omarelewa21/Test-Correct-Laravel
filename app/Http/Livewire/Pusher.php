<?php

namespace tcCore\Http\Livewire;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Livewire\Component;
use tcCore\Events\RemoveFraudDetectionNotification;

class Pusher extends Component implements ShouldBroadcast
{

    protected function getListeners() {
        return [
            'echo-private:TestTake.{$this->testTakeId},.RemoveFraudDetectionNotification' => 'hansactie'
        ];
    }

    public function render()
    {
        return view('livewire.pusher')->layout('layouts.base');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('my-channel');
    }

    public function doeiets() {
        event(new RemoveFraudDetectionNotification('hello world', 1));
    }
    public function hansactie()
    {
        dd('hans!');
    }
}
