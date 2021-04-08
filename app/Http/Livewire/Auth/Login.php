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
    protected function messages(): array
    {
        return [
            'password.required' => __('auth.password_required'),
            'username.required' => __('auth.email_required'),
            'username.email'    => __('auth.email_incorrect'),
        ];
    }


    public function mount()
    {
        if (Auth::check()) {
            return redirect()->intended(route('student.dashboard'));
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        //captcha nodig voor

    if (auth()->attempt($credentials)) {
            $sessionHash = auth()->user()->generateSessionHash();
            session()->put('session_hash', $sessionHash);
            auth()->user()->setAttribute('session_hash', $sessionHash);
            auth()->user()->save();

            return redirect()->intended(route('student.dashboard'));
        }


        return $this->addError('invalid_user', __('auth.failed'));
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.auth');
    }
}
