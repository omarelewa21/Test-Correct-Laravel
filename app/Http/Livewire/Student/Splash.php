<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\AppVersionInfo;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\Info;
use tcCore\Message;
use tcCore\TemporaryLogin;

class Splash extends Component
{

    public function mount()
    {

    }

    public function render()
    {
        return view('livewire.student.splash', [

        ])
            ->layout('layouts.auth');
    }

    public function handleDataAndRedirect()
    {
        AppVersionDetector::handleHeaderCheck();
        AppVersionInfo::createFromSession();
        return redirect()->intended(route('student.dashboard'));
    }

}
