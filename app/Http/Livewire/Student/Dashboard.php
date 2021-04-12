<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    private $testTakes;

    public function mount()
    {
//        $this->testTakes = \tcCore\TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
//            ->where('test_participants.user_id', Auth::id())
//            ->where('test_takes.test_take_status_id', '<=', 3)
//            ->where('test_takes.time_start', '>=', date('y-m-d'))
//            ->paginate(1);
    }

    public function render()
    {
        $this->testTakes = \tcCore\TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', 3)
            ->where('test_takes.time_start', '>=', date('y-m-d'))
            ->paginate(1);
        return view('plan-test-take', ['testTakes' => $this->testTakes])->layout('layouts.base');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect(route('auth.login'));
    }
}
