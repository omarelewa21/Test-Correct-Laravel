<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;

class Navigation extends Component
{
    public $questions;
    public $question;
    public $queryString = ['question'];
    public $showTurnInModal = false;
    public function render()
    {
        return view('livewire.overview.navigation');
    }

    public function updatedQuestion($value)
    {
        $this->dispatchBrowserEvent('current-updated', ['current' => $value]);
    }

    public function turnInModal()
    {
        $this->showTurnInModal = true;
    }
}
