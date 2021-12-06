<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePassword extends Component
{

    public $currentPassword;
    public $currentPasswordRepeat;
    public $newPassword;

    public function rules()
    {
        return [
            'currentPassword'       => 'required',
            'currentPasswordRepeat' => 'required|same:currentPassword',
            'newPassword'           => 'required|min:8|regex:/\d/|regex:/[^a-zA-Z\d]/',
        ];
    }

    public function getMessages()
    {
        return [
            'currentPassword.required'       => __('auth.currentPassword.required'),
            'currentPasswordRepeat.required' => __('auth.currentPasswordRepeat.required'),
            'currentPasswordRepeat.same'     => __('auth.currentPasswordRepeat.same'),
            'newPassword.required'           => __('auth.newPassword.required'),
            'newPassword.min'                => __('auth.newPassword.min'),
            'newPassword.regex'              => __('auth.newPassword.regex'),
        ];
    }

    public function render()
    {
        return view('livewire.change-password');
    }

    public function getMinCharRuleProperty()
    {
        if (empty($this->newPassword)) {
            return null;
        }

        return mb_strlen($this->newPassword) >= 8 ? 'green' : 'red';
    }

    public function getMinDigitRuleProperty()
    {
        if (empty($this->newPassword)) {
            return null;
        }
        return preg_match('/\d/', $this->newPassword) ? 'green' : 'red';
    }

    public function getSpecialCharRuleProperty()
    {
        if (empty($this->newPassword)) {
            return null;
        }
        return preg_match('/[^a-zA-Z\d]/', $this->newPassword) ? 'green' : 'red';
    }

    public function requestPasswordChange()
    {
        $this->validate();
        $user = Auth::user();

        if (Hash::check($this->currentPassword, $user->password)) {
            $user->password = Hash::make($this->newPassword);
            $user->save();

            return $this->dispatchBrowserEvent('password-changed-success', __('auth.password_changed_success'));
        }

        return $this->addError('passwords-dont-match', __('passwords_dont_match'));
    }

    public function updated()
    {
        $this->clearValidation();
    }
}
