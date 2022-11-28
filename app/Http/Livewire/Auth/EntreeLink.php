<?php

namespace tcCore\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ramsey\Uuid\Uuid;

class EntreeLink extends Component
{
    protected $queryString = ['linked', 'with_account'];

    public $with_account, $linked;


    public function mount()
    {
        if (!$this->linked || !Uuid::isValid($this->linked)) {
            return redirect(route('auth.login'));
        }
    }

    public function render()
    {
        return view('livewire.auth.entree-link')
            ->layout('layouts.auth');
    }

    public function logInToTC()
    {
        Auth::user()->redirectToCakeWithTemporaryLogin();
    }
}
