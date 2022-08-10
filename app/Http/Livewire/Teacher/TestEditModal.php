<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Traits\TestActions;
use tcCore\Test;

class TestEditModal extends ModalComponent
{
    use TestActions;

    public $testUuid;
    public $request;

    public $testForChangeAttributes;

    public $placeholder = false;

    public function render()
    {
        return view('livewire.teacher.test-edit-modal');
    }

    public function mount($testUuid)
    {
        $test = Test::whereUuid($testUuid)->firstOrFail();
        $this->testUuid = $testUuid;
        $this->allowedSubjects = $this->getAllowedSubjects();
        $this->allowedTestKinds = $this->getAllowedTestKinds();
        $this->allowedPeriods = $this->getAllowedPeriods();
        $this->allowedEductionLevels = $this->getAllowedEducationLevels();

        [$testKind, $subject, $edLevel, $edYear, $period] = $this->getRequestPropertiesForTest($test);
        $this->request = [
            'name'                 => $test->name,
            'abbreviation'         => $test->abbreviation,
            'test_kind_id'         => $testKind,
            'subject_id'           => $subject,
            'education_level_id'   => $edLevel,
            'education_level_year' => $edYear,
            'period_id'            => $period,
            'shuffle'              => $test->shuffle,
            'introduction'         => $test->introduction,
        ];

        $this->testForChangeAttributes = [
            'subject_id'           => $test->subject_id,
            'education_level_id'   => $test->education_level_id,
            'education_level_year' => $test->education_level_year,
        ];
    }

    private function shouldPromptForDuplicateOrUpdateModal(): bool
    {
        $result = false;
        foreach ($this->testForChangeAttributes as $key => $value) {
            if ($this->request[$key] !== $value) {
                $result = true;
            }
        }
        return $result;
    }


    public function submit()
    {
        $test = Test::whereUuid($this->testUuid)->firstOrFail();
        $this->validate();

        if (!$this->shouldPromptForDuplicateOrUpdateModal()) {
            $this->saveTest($test);

            $this->forceClose()->closeModal();
            $this->emit('testSettingsUpdated', $this->request);
            return true;
        }

        $this->emit('openModal', 'teacher.test-update-or-duplicate-confirm-modal', ['request' => $this->request, 'testUuid' => $this->testUuid]);
    }

    private function saveTest($test)
    {
        $test->fill($this->request);
        $test->save();
    }

    public static function modalMaxWidthClass(): string
    {
        return 'max-w-[600px]';
    }

    public function updatingRequest($value, $name)
    {
        if ($name === 'education_level_id') {
            $this->request['education_level_year'] = 1;
        }
    }

    private function getRequestPropertiesForTest(Test $test): array
    {
        return [
            $this->allowedTestKinds->contains($test->test_kind_id) ? $test->test_kind_id : $this->allowedTestKinds->first()->id,
            $this->allowedSubjects->contains($test->subject_id) ? $test->subject_id : $this->allowedSubjects->first()->id,
            $this->allowedEductionLevels->contains($test->education_level_id) ? $test->education_level_id : $this->allowedEductionLevels->first()->id,
            $this->allowedEductionLevels->contains($test->education_level_id) ? $test->education_level_year : 1,
            $this->allowedPeriods->contains($test->period_id) ? $test->period_id : $this->allowedPeriods->first()->id
        ];
    }
}
