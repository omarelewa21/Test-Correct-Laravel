<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public $testTakes;

    public function mount()
    {
//       select * from test_takes where id in (select test_take_id from test_participants where user_id = 1483 ) and test_take_status_id < 4 and time_start >= DATE('2021-04-07 00:00:00')
        $this->testTakes = \tcCore\TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', 3)
            ->where('test_takes.time_start', '>=', date('y-m-d'))
            ->get();
    }

    public function render()
    {
        return view('plan-test-take')->layout('layouts.base');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect(route('auth.login'));
    }
}
