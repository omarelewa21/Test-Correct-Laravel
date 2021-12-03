<?php

namespace tcCore\Http\Livewire;

use Livewire\Component;

class ChangePassword extends Component
{

    public $currentPassword;
    public $currentPasswordRepeat;
    public $newPassword;

    public function rules()
    {
        return [
            'currentPassword' => 'required',
            'currentPasswordRepeat' => 'required',
            'newPassword' => 'required|min:8|regex:/\d/|regex:/[^a-zA-Z\d]/|same:password_confirmation',
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

        return mb_strlen($this->newPassword) >= 8 ? 'green' : 'red' ;
    }

    public function getMinDigitRuleProperty()
    {
        if (empty($this->newPassword)) {
            return null;
        }
        return preg_match('/\d/', $this->newPassword) ? 'green' : 'red' ;
    }

    public function getSpecialCharRuleProperty()
    {
        if (empty($this->newPassword)) {
            return null;
        }
        return preg_match('/[^a-zA-Z\d]/', $this->newPassword) ? 'green' : 'red' ;
    }

    public function requestPasswordChange()
    {
        $this->validate();

    }
}
