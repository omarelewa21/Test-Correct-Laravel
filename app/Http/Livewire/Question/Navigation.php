<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Livewire\Component;

class Navigation extends Component
{
    public $nav;
    public $q;
    public $queryString = ['q'];

    public function mount()
    {
        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
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
        if ($this->q > 1) {
            $this->q--;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
        }

    }

    public function nextQuestion()
    {
        if ($this->q < $this->nav->count()) {
            $this->q++;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
        }
    }

    public function toOverview()
    {

    }
}
