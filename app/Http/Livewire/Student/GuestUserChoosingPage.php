<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Helpers\BaseHelper;
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
    public $guestData = [];


    protected function getListeners()
    {
        return [
            'echo:TestTake.' . $this->take . ',.TestParticipantGuestAvailabilityChanged' => 'renderGuestList'
        ];
    }

    public function mount()
    {
        if (!Uuid::isValid($this->take)) {
            return redirect(route('auth.login'));
        }
        $this->handleKnownGuests();
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

        if (!$this->claimParticipant($user)) {
            return;
        }

        Auth::login($user);

        BaseHelper::doLoginProcedure();

        redirect(route('student.waiting-room', ['take' => $this->take]));
    }

    public function claimParticipant($user)
    {
        $participant = TestParticipant::whereUserId($user->getKey())->whereTestTakeId($this->testTake->getKey())->first();

        return true;

        // Don't check availability because there is only one option
        if ($participant->available_for_guests) {
            $participant->available_for_guests = false;
            return $participant->save();
        }

        $this->addError('participant_already_taken', __('student.participant_already_taken'));
        $this->renderGuestList();

        return false;
    }

    public function renderGuestList()
    {
        $this->guestList = User::availableGuestAccountsForTake($this->testTake)
            ->whenKnownGuest($this->guestData)
            ->get()
            ->map(function ($guest) {
                return ['name' => $guest->getNameFullAttribute(), 'uuid' => $guest->uuid];
            });
    }

    private function handleKnownGuests()
    {
        $this->guestData = session()->get('guest_data') ?: [];
    }
}
