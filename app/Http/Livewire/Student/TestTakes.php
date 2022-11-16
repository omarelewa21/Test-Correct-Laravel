<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
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
    public $pageNumber;
    const PAGE_NUMBER_KEY = 'student-page-number';


    protected $queryString = ['tab',
        'page' => ['except' => '', 'as' => 'page']
    ];
    public $tab;

    public function mount()
    {
        filled($this->tab) ? $this->changeActiveTab($this->tab) : $this->changeActiveTab($this->plannedTab);
        $this->setPageNumber();
    }

    private function setPageNumber()
    {
        $this->page = request()->get('page');
        session()->put(self::PAGE_NUMBER_KEY, $this->page);
        $this->gotoPage($this->page);
    }

    public function updatedPage()
    {
        session([self::PAGE_NUMBER_KEY => $this->page]);
    }

    public function render()
    {
        return view('livewire.student.test-takes')->layout('layouts.student');
    }

    public function changeActiveTab($tab)
    {
        $this->tab = $tab;
        $this->emitTo("student.$tab", 'tab-selected');
        $this->resetPage();
    }
}
