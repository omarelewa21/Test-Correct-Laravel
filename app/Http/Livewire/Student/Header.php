<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Message;
use tcCore\TemporaryLogin;

class Header extends TCComponent
{
    public $logoUrl;
    public $user_name;
    public $appVersion;
    public $appStatus;
    public $showKnowledgebankModal = false;
    public $unreadMessageCount = null;

    public function mount()
    {
        $this->logoUrl = Auth::user()->guest ? route('auth.login') : route('student.dashboard');
        $this->user_name = Auth::user()->getNameFullAttribute();
        $this->unreadMessageCount = $this->getUnreadMessageCount();
        $this->handleAppVersion();
    }

    public function render()
    {
        return view('livewire.student.header');
    }

    private function handleAppVersion()
    {
        $headers = Session::get('headers');
        $appInfo = AppVersionDetector::detect($headers);

        $this->appVersion = $appInfo['app_version'];
        $this->appStatus = AppVersionDetector::isVersionAllowed($headers);
    }

    public function dashboard()
    {
        return redirect(route('student.dashboard'));
    }

    public function tests()
    {
        return redirect(route('student.test-takes'));
    }

    public function analyses()
    {
        if (auth()->user()->schoolLocation->allow_analyses) {
            return redirect(route('student.analyses.show'));
        }

        $temporaryLogin = TemporaryLogin::createWithOptionsForUser('page', '/analyses/student/'.Auth::user()->uuid, Auth::user());

        return redirect($temporaryLogin->createCakeUrl());
    }

    public function messages()
    {
        $temporaryLogin = TemporaryLogin::createWithOptionsForUser('page', '/messages', Auth::user());

        return redirect($temporaryLogin->createCakeUrl());
    }

    public function knowledgebank()
    {
        $this->showKnowledgebankModal = true;
    }

    public function getUnreadMessageCount()
    {
        return Message::filtered(['unread_receiver_id' => Auth::id() ])->count();
    }

    public function laravelRedirect($route)
    {
        return redirect($route);
    }
}
