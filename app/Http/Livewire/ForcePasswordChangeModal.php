<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use tcCore\Rules\NistPasswordRules;
use tcCore\User;

class ForcePasswordChangeModal extends TCModalComponent
{
    public $newPassword;
    public $newPassword_confirmation;

    protected $preventFieldTransformation = ['newPassword', 'newPassword_confirmation'];

    public function rules()
    {
        return [
            'newPassword' => NistPasswordRules::changePassword(Auth::user()?->username),
        ];
    }

    public function messages()
    {
        return [
            'newPassword.confirmed' => __('password-reset.De twee wachtwoorden zijn niet hetzelfde'),
            'newPassword.min'       => __('password-reset.min password length', ['length' => User::MIN_PASSWORD_LENGTH]),
        ];
    }

    public function render()
    {
        return view('livewire.force-password-change-modal');
    }

    public function requestPasswordChange()
    {
        $user = Auth::user();

        if (Hash::check($this->newPassword, $user->password)) {
            $this->addError('old-and-new-passwords-match', __('auth.old_and_new_passwords_match'));
            return;
        }
        $this->validate();

        $user->password = Hash::make($this->newPassword);
        $user->save();
        $this->closeModal();
        $this->dispatchBrowserEvent('notify',
            [
                'type'    => 'guest_success',
                'title'   => __('auth.password_changed_success'),
                'message' => __('auth.now_login_with_new_password'),
            ]
        );
    }

    public static function modalMaxWidth(): string
    {
        return 'xl';
    }

    public static function closeModalOnEscape(): bool
    {
        return false;
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }
}