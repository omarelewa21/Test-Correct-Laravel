<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestParticipant;

class Dashboard extends Component
{
    use WithStudentTestTakes;

    public function mount()
    {

    }

    public function render()
    {
        return view('livewire.student.dashboard', [
            'testTakes' => $this->getSchedueledTestTakesForStudent(5),
            'ratings'   => $this->getRatingsForStudent(5),
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
}
