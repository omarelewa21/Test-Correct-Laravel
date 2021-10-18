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

    public function mount()
    {
        if (!Uuid::isValid($this->take)) {
            return redirect(route('auth.login'));
        }
        $this->testTake = TestTake::getTestTakeWithSubjectNameAndTestName($this->take);
        $this->participatingClasses = $this->getParticipatingClasses($this->testTake);

        $this->sortField = 'users.name';
        $this->sortDirection = 'desc';

        $this->renderGuestList();
    }

    public function render()
    {
        $this->testTake = TestTake::getTestTakeWithSubjectNameAndTestName($this->take);
        return view('livewire.student.guest-graded-overview')->layout('layouts.auth');
    }

    private function getAvailableGuestAccountsForTake($sortField, $sortDirection)
    {
        return User::select('users.uuid', 'users.name', 'users.name_first', 'users.name_suffix', 'test_participants.rating')
            ->guests()
            ->leftJoin('test_participants', 'test_participants.user_id', '=', 'users.id')
            ->where('test_participants.test_take_id', $this->testTake->getKey())
            ->orderBy($sortField, $sortDirection);
    }

    public function renderGuestList($sortField = 'users.name', $sortDirection = 'desc')
    {
        $this->guestList = [];
        $guests = $this->getAvailableGuestAccountsForTake($sortField, $sortDirection);

        $guests->each(function ($guest) {
            $this->guestList[] = [
                'name'   => $guest->getNameFullAttribute(),
                'uuid'   => $guest->uuid,
                'rating' => $guest->rating,
            ];
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
}
