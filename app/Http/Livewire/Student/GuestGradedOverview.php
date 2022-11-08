<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;

class GuestGradedOverview extends Component
{
    use WithStudentTestTakes, WithSorting;

    protected $queryString = ['take'];
    public $take;
    public $testTake;
    public $guestList = [];
    public $participatingClasses = [];
    public $test;

    public function mount()
    {
        if (!Uuid::isValid($this->take)) {
            return redirect(route('auth.login'));
        }
        $this->handleKnownGuests();
        $this->testTake = TestTake::getTestTakeWithSubjectNameAndTestName($this->take);
        $this->participatingClasses = $this->getParticipatingClasses($this->testTake);

        $this->sortField = 'users.name';
        $this->sortDirection = 'desc';

        $this->renderGuestList();
    }

    public function render()
    {
        $this->testTake = TestTake::getTestTakeWithSubjectNameAndTestName($this->take);
        //$this->testTake = $this->testTake->makeHidden(['hide_grades']);
        return view('livewire.student.guest-graded-overview')->layout('layouts.auth');
    }

    public function renderGuestList($sortField = 'users.name', $sortDirection = 'desc')
    {
        $this->guestList = User::availableGuestAccountsForTake($this->testTake)
            ->whenKnownGuest($this->guestData)
            ->orderBy($sortField, $sortDirection)
            ->get()
            ->map(function ($guest) {
                return ['name' => $guest->getNameFullAttribute(), 'uuid' => $guest->uuid, 'rating' => $guest->rating];
            });
    }

    public function sortGuestNames()
    {
        $this->sortBy('users.name');
        $this->renderGuestList($this->sortField, $this->sortDirection);
    }
    public function sortGuestGrades()
    {
        $this->sortBy('test_participants.rating');
        $this->renderGuestList($this->sortField, $this->sortDirection);
    }

    public function continueAs($userUuid)
    {
        if (!$this->canReviewTestTake()) {
            return $this->addError('reviewing_time_has_expired', __('student.reviewing_time_has_expired'));
        }
        $user = User::whereUuid($userUuid)->firstOrFail();

        Auth::login($user);
        $sessionHash = $user->generateSessionHash();
        $user->setSessionHash($sessionHash);

        redirect(route('student.waiting-room', ['take' => $this->take, 'directly_to_review' => true]));
    }

    public function canReviewTestTake()
    {
        return $this->testTake->reviewingIsPossible();
    }

    private function handleKnownGuests()
    {
        $this->guestData = session()->get('guest_data') ?: [];
    }
}
