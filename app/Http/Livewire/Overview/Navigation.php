<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;

class Navigation extends Component
{
    public $questions;
    public $question;
    public $queryString = ['question'];

    public function render()
    {
        return view('livewire.question.navigation');
    }

    public function updatedQuestion($value)
    {
        $this->dispatchBrowserEvent('current-updated', ['current' => $value]);
    }
}
