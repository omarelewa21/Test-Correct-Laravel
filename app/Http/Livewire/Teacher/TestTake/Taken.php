<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\AnonymousComponent;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\Http\Livewire\Teacher\TestTakeWarningModal;
use tcCore\Invigilator;
use tcCore\Log;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTake as TestTakeModel;
use tcCore\User;

class Taken extends TestTakeComponent
{
    public $dropdownData = [];
    public $selected = [];
    public Collection $invigilatorUsers;

    public function mount(TestTakeModel $testTake): void
    {
        parent::mount($testTake);
    }

    public function refresh(): void
    {
        $this->fillGridData();
        $this->setStudentData();
        $this->setInvigilators();
    }

    public function redirectToOverview()
    {
        return CakeRedirectHelper::redirectToCake('planned.my_tests');
    }
    public function surveillance(): void
    {
        CakeRedirectHelper::redirectToCake('planned.surveillance');
    }
}