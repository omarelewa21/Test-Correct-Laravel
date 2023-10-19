<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use tcCore\Http\Controllers\FileManagementUsersController;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\Choices\ChildChoice;
use tcCore\Http\Helpers\Choices\ParentChoice;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Livewire\Teacher\TestTake\Planned;
use tcCore\Http\Traits\Modal\WithPlanningFeatures;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;

class TestTakeWarningModal extends TCModalComponent
{
    public TestTake $testTake;
    public Collection $displayWarnings;

    public function mount(TestTake $testTake, $warnings)
    {
        if(!Gate::allows('isAllowedToViewTestTake',[$testTake])){
            $this->forceClose()->closeModal();
            return;
        }

        $this->testTake = $testTake;
        $this->displayWarnings = collect();
        $this->setWarningData($warnings);
    }

    private function setWarningData($warnings): void
    {
        collect($warnings)->each(function ($warning, $key) {
            $this->displayWarnings->push($this->warningLookup()[$key]);
        });
    }

    private function warningLookup(): array
    {
        return [
            'browser_testing'         => [
                'title' => __('test-take.Beveiligde student app niet verplicht'),
                'body'  => __(
                    'test-take.De student kan de toets in de browser maken. Bij toetsen in de browser kunnen wij het gebruik van andere apps niet blokkeren.'
                ),
            ],
            'guest_accounts'          => [
                'title' => __('test-take.Test-Direct toestaan'),
                'body'  => __(
                    'test-take.De student kan inloggen met een Test-Direct account (en de toetscode) om de toets te maken, beoordelen, in te zien, en het cijfer te bekijken.'
                ),
            ],
            'participants_incomplete' => [
                'title' => __('test-take.Niet alle Studenten zijn aanwezig.'),
            ],
        ];
    }

    public function continue(): void
    {
        $this->testTake->startTake();
        CakeRedirectHelper::redirectToCake('planned.surveillance');
    }
}
