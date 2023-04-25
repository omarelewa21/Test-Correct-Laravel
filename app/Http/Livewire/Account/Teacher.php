<?php

namespace tcCore\Http\Livewire\Account;

use Livewire\Component;
use tcCore\Http\Traits\WithReturnHandling;
use tcCore\User;

class Teacher extends Component
{
    use WithReturnHandling;
    private User $user;
    public string $userUuid;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->userUuid = $user->uuid;
    }

    public function redirectBack()
    {
        return $this->redirectUsingReferrer();
    }
}
