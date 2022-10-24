<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;

class Footer extends Component
{
    public $nextAnswerAvailable = false;

    public function updateAnswerRating()
    {
        $this->emit('updateAnswerRating');
    }

    public function render()
    {
        return view('livewire.co-learning.footer');
    }
}
