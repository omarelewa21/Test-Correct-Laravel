<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use tcCore\Http\Traits\UserNotificationForController;
use tcCore\User;

class ChangePassword extends Component
{
    use UserNotificationForController;

    public $currentPassword;
    public $newPassword;
    public $newPasswordRepeat;

    public function rules()
    {
        return [
            'currentPassword'   => 'required',
            'newPasswordRepeat' => 'required|same:newPassword',
            'newPassword'       => 'required|'. User::getPasswordLengthRule(),
        ];
    }

    public function getMessages()
    {
        return [
            'currentPassword.required'   => __('auth.currentPassword.required'),
            'newPasswordRepeat.required' => __('auth.newPasswordRepeat.required'),
            'newPasswordRepeat.same'     => __('auth.newPasswordRepeat.same'),
            'newPassword.required'       => __('auth.newPassword.required'),
            'newPassword.min'            => __('auth.newPassword.min'),
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

    public function requestPasswordChange()
    {
        $this->validate();
        $user = Auth::user();

        if (!Hash::check($this->currentPassword, $user->password)) {
            return $this->addError('passwords-dont-match', __('auth.passwords_dont_match'));
        }

        $user->password = Hash::make($this->newPassword);
        $user->save();
        $this->sendPasswordChangedMail($user);
        return $this->dispatchBrowserEvent('password-changed-success', __('auth.password_changed_success'));
    }

    public function updated()
    {
        $this->clearValidation();
    }
}
