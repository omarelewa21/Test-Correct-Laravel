<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithStudentTestTakes;

class TestTakes extends Component
{
    use WithPagination, WithStudentTestTakes;

    public $plannedTab = 'planned';
    public $discussTab = 'discuss';
    public $reviewTab = 'review';
    public $gradedTab = 'graded';

    protected $queryString = ['tab'];
    public $tab;

    public function mount()
    {
        filled($this->tab) ? $this->changeActiveTab($this->tab) : $this->changeActiveTab($this->plannedTab);
    }

    public function render()
    {
        return view('livewire.student.test-takes')->layout('layouts.student');
    }

    public function changeActiveTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();

    }

    private function goToTab()
    {
        if($this->tab === 'planned') $this->changeActiveTab($this->plannedTab);
        if($this->tab === 'discuss') $this->changeActiveTab($this->discussTab);
        if($this->tab === 'review') $this->changeActiveTab($this->reviewTab);
        if($this->tab === 'graded') $this->changeActiveTab($this->gradedTab);

    }
}
