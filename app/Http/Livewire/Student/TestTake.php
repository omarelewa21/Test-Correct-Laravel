<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\TestTake as Test;


class TestTake extends Component
{
    public $testTake;
    public $question = 1;
    protected $queryString = ['question'];

    public function mount(Test $testTake)
    {
        $this->testTake = $testTake;
    }

    public function render()
    {
        return view('student.test-take');
    }

}