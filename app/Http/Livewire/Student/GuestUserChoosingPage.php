<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;

class GuestUserChoosingPage extends Component
{
    use WithStudentTestTakes;

    protected $queryString = ['take'];
    public $take;
    public $testTake;
    public $guestList = [];
    public $status;
    public $participatingClasses = [];


    protected function getListeners()
    {
        return [
            'echo:TestTake.'.$this->take.',.TestParticipantGuestAvailabilityChanged' => 'renderGuestList'
        ];
    }

    public function mount()
    {
        if (!Uuid::isValid($this->take)) {
            return redirect(route('auth.login'));
        }
        $this->testTake = TestTake::getTestTakeWithSubjectNameAndTestName($this->take);
        $this->participatingClasses = $this->getParticipatingClasses($this->testTake);
        $this->status = $this->testTake->determineTestTakeStage();
        $this->renderGuestList();
    }

    public function render()
    {
        $this->testTake = TestTake::getTestTakeWithSubjectNameAndTestName($this->take);
        return view('livewire.student.guest-user-choosing-page')->layout('layouts.auth');
    }

    public function continueAs($userUuid)
    {
        $user = User::whereUuid($userUuid)->firstOrFail();

        if(!$this->claimParticipant($user)) {
            return;
        }

        Auth::login($user);

        $sessionHash = $user->generateSessionHash();
        $user->setSessionHash($sessionHash);

        redirect(route('student.waiting-room', ['take' => $this->take]));
    }

    public function claimParticipant($user)
    {
        $participant = TestParticipant::whereUserId($user->getKey())->whereTestTakeId($this->testTake->getKey())->first();

        if ($participant->available_for_guests) {
            $participant->available_for_guests = false;
            return $participant->save();
        }

        $this->addError('participant_already_taken', 'student.particpant_already_taken');
        $this->renderGuestList();

        return false;
    }

    private function getAvailableGuestAccountsForTake($testTake)
    {
        return User::select('users.uuid','users.name','users.name_first','users.name_suffix')
            ->guests()
            ->leftJoin('test_participants', 'test_participants.user_id', '=', 'users.id')
            ->where('test_participants.test_take_id', $this->testTake->getKey())
            ->where('test_participants.available_for_guests', true);
    }

    public function renderGuestList()
    {
        $this->guestList = [];
        $guests = $this->getAvailableGuestAccountsForTake($this->testTake);

        $guests->each(function ($guest) {
            $this->guestList[] = ['name' => $guest->getNameFullAttribute(), 'uuid' => $guest->uuid];
        });
    }
}
