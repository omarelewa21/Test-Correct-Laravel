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

    public function mount()
    {
        if (!$this->q) {
            $this->q = 1;
        }
        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
    }


    public function render()
    {
        return view('livewire.question.navigation');
    }

    private function getDetailsFirstQuestion() {
        return ['data' => ['prev' => false, 'next' => true, 'turnin' => false]];
    }

    private function getDetailsLastQuestion() {
        return ['data' => ['prev' => true, 'next' => false, 'turnin' => true]];
    }

    private function getDetailsQuestion() {
        return ['data' => ['prev' => true, 'next' => true, 'turnin' => false]];
    }

    public function updatedQ($value)
    {
        $details = $this->getDetailsQuestion();

        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }

        if($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }

        $this->dispatchBrowserEvent('update-footer-navigation', $details);
    }

    public function previousQuestion()
    {
        if ($this->q > 1) {
            $this->q--;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);

    }

    public function nextQuestion()
    {
        if ($this->q < $this->nav->count()) {
            $this->q++;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);
    }

    public function toOverview()
    {
        return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
    }

    public function updateQuestionIndicatorColor()
    {
        $newNav = $this->nav->map(function (&$item, $key) {
            $q = $this->q;
            if ($key == --$q) {
                $item['answered'] = true;
                return $item;
            }
            return $item;
        });
        $this->nav = $newNav;
    }
}
