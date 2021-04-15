<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public function mount()
    {

    }

    public function render()
    {
        return view('livewire.student.dashboard', [
            'testTakes' => $this->fetchTestTakes(),
        ])
            ->layout('layouts.student');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect(route('auth.login'));
    }

    private function fetchTestTakes()
    {
        return \tcCore\TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', 3)
            ->where('test_takes.time_start', '>=', date('y-m-d'))
            ->orderBy('test_takes.time_start', 'ASC')
            ->paginate(10);
    }

    public function giveInvigilatorNamesFor(\tcCore\TestTake $testTake)
    {
        $invigilators = [];
        $invigilators = $testTake->invigilatorUsers->map(function ($invigilator) {
            return $invigilator->getFullNameWithAbbreviatedFirstName();
        });

        return collect($invigilators);
    }

    public function startTestTake($uuid)
    {
        $this->redirect(route('student.test-take-laravel', $uuid));
    }
}
