<?php

namespace tcCore\Http\Livewire\Student;

use tcCore\AppVersionInfo;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Livewire\TCComponent;

class Splash extends TCComponent
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
