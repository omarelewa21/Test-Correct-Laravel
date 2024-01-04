<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use tcCore\Events\NewTestTakeGraded;
use tcCore\Events\NewTestTakePlanned;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\Info;
use tcCore\Message;
use tcCore\TemporaryLogin;

class Dashboard extends TCComponent
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
            'testTakes'      => $this->getScheduledTestTakesForStudent(5),
            'ratedTestTakes' => $this->getRatingsForStudent(null, 5, 'test_takes.updated_at', 'desc', false),
            'messages'       => $this->getMessages(),
        ])
            ->layout('layouts.student');
    }

    public function logout()
    {
        UserHelper::logout();
        return redirect(
            BaseHelper::getLogoutUrl()
        );
        // @TODO MF 21-12-2021 remove redirect statement; this is still here because I don't know the function of the device parameter;
       // return redirect(route('auth.login', ['device' => $device]));
    }

    public function getMessages()
    {
        return Message::filtered(['receiver_id' => Auth::id()])->orderBy('created_at', 'desc')->take(3)->get();
    }

    public function getInfoMessages()
    {
        return Info::getForUser(Auth::user());
    }

    public function showAppVersionMessage()
    {
        if (session()->get('TLCVersion', 'x') != 'x' && session()->get('TLCVersioncheckResult') != 'OK') {
            $res = AppVersionDetector::needsUpdateDeadline(session()->get('headers'));
            if ($res !== false) {
                $this->needsUpdateDeadline = $res->isoFormat('LL');
                return true;
            }
            return false;
        }

        return false;
    }

    public function readMessages()
    {
        $temporaryLogin = TemporaryLogin::createWithOptionsForUser('page', '/messages', Auth::user());
        return redirect($temporaryLogin->createCakeUrl());
    }
}
