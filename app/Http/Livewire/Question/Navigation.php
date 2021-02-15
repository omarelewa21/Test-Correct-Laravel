<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Livewire\Component;

class Navigation extends Component
{
    public $nav;
    public $testTakeUuid;
    public $q;
    public $queryString = ['q'];

    public $showTurnInModal = false;

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
        if ($this->q == 1) {
            $this->dispatchBrowserEvent('update-footer-navigation', ['data' => ['prev' => false, 'next' => true, 'turnin' => false]]);
        } elseif($this->q == $this->nav->count()) {
            $this->dispatchBrowserEvent('update-footer-navigation', ['data' => ['prev' => true, 'next' => false, 'turnin' => true]]);
        } else {
            $this->dispatchBrowserEvent('update-footer-navigation', ['data' => ['prev' => true, 'next' => true, 'turnin' => false]]);
        }
    }

    public function previousQuestion()
    {

        if ($this->q > 1) {
            $this->q--;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
            $this->dispatchBrowserEvent('update-footer-navigation', ['data' => ['prev' => true, 'next' => true, 'turnin' => false]]);
        }
        if ($this->q == 1) {
            $this->dispatchBrowserEvent('update-footer-navigation', ['data' => ['prev' => false, 'next' => true, 'turnin' => false]]);
        }

    }

    public function nextQuestion()
    {
        if ($this->q < $this->nav->count()) {
            $this->q++;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
            $this->dispatchBrowserEvent('update-footer-navigation', ['data' => ['prev' => true, 'next' => true, 'turnin' => false]]);
        }
        if ($this->q == $this->nav->count()) {
            $this->dispatchBrowserEvent('update-footer-navigation', ['data' => ['prev' => true, 'next' => false, 'turnin' => true]]);

        }
    }

    public function toOverview()
    {
        return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
    }

    public function turnInModal()
    {
        $this->showTurnInModal = true;
    }
}
