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
    private function get_browser_language(){
        if(array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)){
            $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if($language ==	 'nl'){
                return 'nl';
            }
        }
        return 'eng';
    }
    protected function messages(){
        if($this->get_browser_language() == 'nl'){
            return[
                'password.required' => 'Wachtwoord is verplicht',
                'username.required' => 'E-mailadres is verplicht',
                'username.email'    => 'E-mailadres is niet geldig',
            ];
        }
        return[
            'password.required' => 'Password is required',
            'username.required' => 'Email address is required',
            'username.email'    => 'Email address is not valid',
        ];
    }

    public function mount()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        //Redirect to portal for now, not supposed to be accessed by public.
        $this->redirect(config('app.url_login'));
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
