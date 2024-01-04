<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\WithPagination;
use tcCore\Events\NewTestTakePlanned;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;

class Planned extends TCComponent
{
    use WithPagination, WithStudentTestTakes, WithSorting;

    private $testTakes;

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
    }

    public function render()
    {
        return view('livewire.student.planned', [
            'testTakes' => $this->getScheduledTestTakesForStudent(null, 6, $this->sortField, $this->sortDirection)
        ]);
    }
}
