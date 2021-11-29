<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\Info;
use tcCore\Message;

class Dashboard extends Component
{
    use WithPagination,WithStudentTestTakes;

    public $infos = [];

    public function mount()
    {
        $this->infos = $this->getInfoMessages();
    }

    public function render()
    {
        return view('livewire.student.dashboard', [
            'testTakes' => $this->getSchedueledTestTakesForStudent(5),
            'testParticipants'   => $this->getRatingsForStudent(5),
            'messages' => $this->getMessages(),
        ])
            ->layout('layouts.student');
    }

    public function logout()
    {
        $device = session()->get('TLCOs') == 'iOS' ? 'ipad' : '';
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect(route('auth.login', ['device' => $device]));
    }

    public function getMessages()
    {
        return Message::filtered(['receiver_id' => Auth::id() ])->orderBy('created_at', 'desc')->take(3)->get();
    }

    public function getInfoMessages()
    {
        return Info::getInfoForUser(Auth::user());
    }
}
