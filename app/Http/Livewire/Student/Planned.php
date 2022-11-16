<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Events\NewTestTakePlanned;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;

class Planned extends Component
{
    use WithPagination, WithStudentTestTakes, WithSorting;
    const PAGE_NUMBER_KEY = 'student-page-number';

    private $testTakes;

    protected $queryString = [
    'page' => ['except' => '', 'as' => 'page']
    ];

    protected function getListeners()
    {
        return [
            NewTestTakePlanned::channelSignature() => '$refresh',
        ];
    }

    public function mount()
    {
        $this->sortField = 'test_takes.time_start';
        $this->sortDirection = 'ASC';
        $this->setPageNumber();
    }

    private function setPageNumber()
    {
        $page = request()->get('page');
        $this->page = $page;
        $this->gotoPage($page);
    }

    public function updatedPage()
    {
        session([self::PAGE_NUMBER_KEY => $this->page]);
    }
    public function render()
    {
        return view('livewire.student.planned', [
            'testTakes' => $this->getSchedueledTestTakesForStudent(null, 6, $this->sortField, $this->sortDirection),
            'page' => $this->page
        ]);
    }
}
