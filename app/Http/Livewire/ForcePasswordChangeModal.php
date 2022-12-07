<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LivewireUI\Modal\ModalComponent;
use tcCore\User;

class ForcePasswordChangeModal extends ModalComponent
{
    public $oldPassword;
    public $newPassword;
    public $newPasswordRepeat;

    public function rules()
    {
        return [
            'oldPassword' => 'required',
            'newPasswordRepeat' => 'required|same:newPassword',
            'newPassword'       => 'required|'. User::getPasswordLengthRule(),
        ];
    }

    public function render()
    {
        return view('livewire.force-password-change-modal');
    }

    public function requestPasswordChange()
    {
        $user = Auth::user();

        if(!Hash::check($this->oldPassword, $user->password)) {
            $this->addError('old-password-wrong', __('auth.passwords_dont_match'));
            return;
        }
        if(Hash::check($this->newPassword, $user->password)) {
            $this->addError('old-and-new-passwords-match', __('auth.old_and_new_passwords_match'));
            return;
        }
        $this->validate();

        $user->password = Hash::make($this->newPassword);
        $user->save();
        $this->closeModal();
        $this->dispatchBrowserEvent('notify',
            [
                'type' => 'guest_success',
                'title' => __('auth.password_changed_success'),
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