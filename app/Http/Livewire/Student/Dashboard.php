<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithPersonalizedTestTakes;

class Dashboard extends Component
{
    use WithPagination, WithPersonalizedTestTakes;

    public function mount()
    {

    }

    public function render()
    {
        return view('livewire.student.dashboard', [
            'testTakes' => $this->fetchTestTakes(5),
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
