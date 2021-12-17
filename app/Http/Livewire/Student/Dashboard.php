<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Events\NewTestTakeGraded;
use tcCore\Events\NewTestTakePlanned;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\Info;
use tcCore\Message;
use tcCore\TemporaryLogin;

class Dashboard extends Component
{
    use WithPagination, WithStudentTestTakes;

    public $infos = [];

    public $needsUpdateDeadline;
    public $showKnowledgebankAppNotificationModal = false;

    protected function getListeners()
    {
        return [
            NewTestTakePlanned::channelSignature() => '$refresh',
            NewTestTakeGraded::channelSignature()  => '$refresh',
        ];
    }

    public function mount()
    {
        $this->infos = $this->getInfoMessages();
    }

    public function render()
    {
        return view('livewire.student.dashboard', [
            'testTakes'      => $this->getSchedueledTestTakesForStudent(5),
            'ratedTestTakes' => $this->getRatingsForStudent(5, null, null, null, false),
            'messages'       => $this->getMessages(),
        ])
            ->layout('layouts.student');
    }

    public function logout()
    {
        $device = session()->get('TLCOs') == 'iOS' ? 'ipad' : '';
        UserHelper::logout();

        return redirect(route('auth.login', ['device' => $device]));
    }

    public function getMessages()
    {
        return Message::filtered(['receiver_id' => Auth::id()])->orderBy('created_at', 'desc')->take(3)->get();
    }

    public function getInfoMessages()
    {
        return Info::getInfoForUser(Auth::user());
    }

    public function showAppVersionMessage()
    {
        if (session()->get('TLCVersion', 'x') != 'x' && session()->get('TLCVersioncheckResult') != 'OK') {
            $this->needsUpdateDeadline = AppVersionDetector::needsUpdateDeadline(session()->get('headers'));
            return true;
        }

        return false;
    }

    public function readMessages()
    {
        $temporaryLogin = TemporaryLogin::createWithOptionsForUser('page', '/messages', Auth::user());
        return redirect($temporaryLogin->createCakeUrl());
    }
}
