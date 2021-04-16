<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\Http\Traits\WithPersonalizedTestTakes;

class Tests extends Component
{
    use WithPersonalizedTestTakes;

    const PLANNED_TAB = 1;
    const DISCUSS_TAB = 2;
    const REVIEW_TAB = 3;
    const GRADED_TAB = 4;
    public $activeTab;

    public function mount()
    {
        $this->activeTab = self::PLANNED_TAB;
    }

    public function render()
    {
        return view('livewire.student.tests', ['testTakes' => $this->fetchTestTakes()])->layout('layouts.student');
    }

    public function changeActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
}
