<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LivewireUI\Modal\ModalComponent;
use tcCore\User;

class ForcePasswordChangeModal extends ModalComponent
{
    public $newPassword;
    public $newPasswordRepeat;

    public function rules()
    {
        return [
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
        $this->validate();

        $user = Auth::user();
        $user->password = Hash::make($this->newPassword);
        $user->save();
        $this->closeModal();
        return $this->dispatchBrowserEvent('password-changed-success', __('auth.password_changed_success'));
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