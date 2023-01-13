<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\TestTake;

class CoLearning extends Component
{
    public int|TestTake $testTake;

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;
    }

    public function render()
    {
        return view('livewire.teacher.co-learning')
            ->layout('layouts.co-learning-teacher');
    }

    public function redirectBack()
    {
        return redirect()->route('teacher.test-takes', ['stage' => 'taken']);
    }
}
