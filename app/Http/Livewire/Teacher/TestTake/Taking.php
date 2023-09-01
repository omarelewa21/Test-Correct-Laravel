<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Livewire\Teacher\TestTake\TestTake as TestTakeComponent;
use tcCore\TestTake as TestTakeModel;

class Taking extends TestTakeComponent
{
    public array $selected = [];
    public Collection $invigilatorUsers;

    public function mount(TestTakeModel $testTake): void
    {
        if (Gate::denies('canUsePlannedTestPage')) {
            TestTakeModel::redirectToDetail($testTake->uuid);
        }
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

    public function breadcrumbTitle(): string
    {
        return __('general.Mijn afgenomen toetsen');
    }
}