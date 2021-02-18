<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;
use tcCore\TemporaryLogin;
use tcCore\TestParticipant;
use tcCore\User;


class TestTake extends Component
{
    public $testTakeUuid;
    public $showTurnInModal = false;
    public $questions;

    public function render()
    {
        return view('livewire.student.test-take');
    }

    public function turnInModal()
    {
        $this->showTurnInModal = true;
    }

    public function toOverview()
    {
        return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
    }

    public function TurnInTestTake()
    {
        $testTake = \tcCore\TestTake::whereUuid($this->testTakeUuid)->first();
        $testParticipant = TestParticipant::where('test_take_id', $testTake->id)->where('user_id', Auth::id())->first();

        if (!$testParticipant->handInTestTake()) {
            dd('gefaald');
        }

        $temporaryLogin = TemporaryLogin::create(
            ['user_id' => $testParticipant->user_id]
        );
        $redirectUrl = $temporaryLogin->createCakeUrl();

        return redirect()->to($redirectUrl);
    }

}
