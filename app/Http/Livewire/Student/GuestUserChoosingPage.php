<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\TestTake;
use tcCore\User;

class GuestUserChoosingPage extends Component
{

    protected $queryString = ['take'];
    public $take;
    public $testTake;
    public $guestList;

    public function mount()
    {
        if (!Uuid::isValid($this->take)) {
            return redirect(route('auth.login'));
        }
        $this->testTake = TestTake::getTestTakeWithSubjectNameAndTestName($this->take);

        $guests = User::select('users.uuid','users.name','users.name_first','users.name_suffix')
            ->guests()
            ->leftJoin('test_participants', 'test_participants.user_id', '=', 'users.id')
            ->where('test_participants.test_take_id', $this->testTake->getKey());

        $guests->each(function ($guest) {
           $this->guestList[] = ['name' => $guest->getNameFullAttribute(), 'uuid' => $guest->uuid];
        });
    }

    public function render()
    {
        return view('livewire.student.guest-user-choosing-page')->layout('layouts.auth');
    }

    public function continueAs($userUuid)
    {
        $user = User::whereUuid($userUuid)->first();
        session()->flush();

        Auth::login($user);
        redirect(route('student.waiting-room'), ['take' => $this->take]);
    }
}
