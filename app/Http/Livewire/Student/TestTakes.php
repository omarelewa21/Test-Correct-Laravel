<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithStudentTestTakes;

class TestTakes extends Component
{
    use WithPagination, WithStudentTestTakes;

    public $plannedTab = 1;
    public $discussTab = 2;
    public $reviewTab = 3;
    public $gradedTab = 4;
    public $activeTab;

    protected $queryString = [
        'tab'         => ['except' => ''],
    ];
    public $tab;

    public function mount()
    {
        $this->activeTab = $this->plannedTab;
        if ($this->tab) {
            $this->goToTab();
        }
    }

    public function render()
    {
        return view('livewire.student.test-takes')->layout('layouts.student');
    }

    public function changeActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    private function goToTab()
    {
        if($this->tab === 'planned') $this->changeActiveTab($this->plannedTab);
        if($this->tab === 'discuss') $this->changeActiveTab($this->discussTab);
        if($this->tab === 'review') $this->changeActiveTab($this->reviewTab);
        if($this->tab === 'graded') $this->changeActiveTab($this->gradedTab);
        $this->reset('tab');
    }
}
