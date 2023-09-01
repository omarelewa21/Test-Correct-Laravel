<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Traits\Modal\WithPlanningFeatures;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\UserFeatureSetting;

class TestQuickTakeModal extends TCModalComponent
{
    use WithPlanningFeatures;

    private $test;
    public string $testUuid;
    public string $testName;

    public $testTake;

    public $clickDisabled = false;


    protected function messages(): array
    {
        return [
            'classesAndStudents.children.filled' => __('validation.school_class_or_guest_accounts_required'),
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'testTake.weight'                  => __('teacher.Weging'),
            'testTake.allow_inbrowser_testing' => __('teacher.Browsertoetsen toestaan'),
            'testTake.guest_accounts'          => __('teacher.Test-Direct toestaan'),
        ];
    }

    protected function rules(): array
    {
        return $this->getConditionalRules() + [
                'testTake.weight'                  => 'required|numeric',
                'testTake.allow_inbrowser_testing' => 'required|boolean',
                'testTake.guest_accounts'          => 'required|boolean',
                'testTake.notify_students'         => 'required|boolean',
                'testTake.show_grades'             => 'sometimes|boolean',
                'testTake.show_correction_model'   => 'sometimes|boolean',
            ];
    }

    private function getConditionalRules(): array
    {
        $conditionalRules = [];
        if (!$this->testTake->guest_accounts) {
            $conditionalRules['classesAndStudents.children'] = 'filled';
        }
        return $conditionalRules;
    }

    public function mount($testUuid)
    {
        $this->testUuid = $testUuid;
        $this->test = Test::whereUuid($this->testUuid)->firstOrFail();
        $this->testName = $this->test->name;

        $this->testTake = new TestTake();
        $this->setFeatureSettingDefaults($this->testTake);
        $this->testTake->is_rtti_test_take = false;
    }

    public function hydrate()
    {
        $this->test = Test::whereUuid($this->testUuid)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.teacher.test-quick-take-modal');
    }

    public function plan()
    {
        $this->setDefaultTestTakeSettings();
        $this->validate();
        $this->clickDisabled = true;
        $this->testTake->save();

        $this->handleParticipants($this->testTake);

        $this->dispatchBrowserEvent('notify', ['message' => __('teacher.testtake planned')]);
        $this->closeModal();

        $detailUrl = sprintf('test_takes/view/%s', $this->testTake->uuid);
        $temporaryLogin = TemporaryLogin::createWithOptionsForUser('page', $detailUrl, auth()->user());
        $this->redirect($temporaryLogin->createCakeUrl());
    }

    private function setDefaultTestTakeSettings()
    {
        $this->testTake->test_id = $this->test->getKey();
        $this->testTake->test_take_status_id = TestTakeStatus::STATUS_PLANNED;
        $this->testTake->period_id = PeriodRepository::getCurrentPeriod()->getKey();
        $this->testTake->time_start = Carbon::today();
        $this->testTake->user_id = auth()->id();
        $this->testTake->uuid = Uuid::uuid4();

        $this->testTake->fill([
            'invigilators' => [auth()->id()],
//            'school_classes' => $this->selectedClasses
        ]);
    }

    public function validate($rules = null, $messages = [], $attributes = []): array
    {
        return parent::validate();
    }
}
