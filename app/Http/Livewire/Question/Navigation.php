<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;

class Navigation extends Component
{
    public $questions;
    public $q;
    public $queryString = ['q'];

    public function mount()
    {
//        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
    }

    public function render()
    {
        return view('livewire.question.navigation');
    }

    public function updatedQ($value)
    {
//        $this->dispatchBrowserEvent('current-updated', ['current' => $value]);
    }

    public function previousQuestion()
    {
        $this->q --;
        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);

    }

    public function nextQuestion() {

        $this->q ++;
        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
    }
}
