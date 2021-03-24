<?php

namespace tcCore\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public $username = '';
    public $password = '';

    protected $rules = [
        'username' => 'required|email',
        'password' => 'required',
    ];
    protected $messages = [
        'password.required' => 'Wachtwoord is verplicht',
        'username.required' => 'E-mailadres is verplicht',
        'username.email'    => 'E-mailadres is niet geldig',
    ];

    public function mount()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            auth()->user()->setAttribute('session_hash', auth()->user()->generateSessionHash());
            auth()->user()->save();
            return redirect()->intended(route('student.test-take'));
        }
        return $this->addError('username', __('auth.failed'));
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.auth');
    }
}
