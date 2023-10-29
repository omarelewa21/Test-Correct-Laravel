<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use tcCore\Http\Traits\UserNotificationForController;
use tcCore\Rules\NistPasswordRules;
use tcCore\User;

class ChangePassword extends TCModalComponent
{
    use UserNotificationForController;

    public $currentPassword;
    public $newPassword;
    public $newPassword_confirmation;
    protected $preventFieldTransformation = ['newPassword', 'newPassword_confirmation', 'currentPassword'];

    public function rules()
    {
        return [
            'currentPassword'   => 'required',
            'newPassword_confirmation' => 'required',
            'newPassword'       => NistPasswordRules::changePassword(
                username: auth()->user()->username,
                oldPassword: $this->currentPassword
            ),
        ];
    }

    public function getMessages()
    {
        return [
            'currentPassword.required'   => __('auth.currentPassword.required'),
            'newPassword_confirmation.required' => __('auth.newPasswordRepeat.required'),
            'newPassword.required'       => __('auth.newPassword.required'),
            'newPassword.min'            => __('auth.newPassword.min'),
            'newPassword.confirmed'            => __('auth.newPasswordRepeat.same'),
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

        $user->password = $this->newPassword;
        $user->save();
        $this->sendPasswordChangedMail($user);

        $this->dispatchBrowserEvent('notify', ['message' => __('auth.password_changed_success')]);
        $this->closeModal();
    }

    public function updated()
    {
        $this->clearValidation();
    }

    public static function modalMaxWidth(): string
    {
        return 'md';
    }
}
