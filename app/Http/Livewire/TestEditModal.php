<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use tcCore\Period;
use tcCore\Test;

abstract class TestEditModal extends TestModal
{
    public $testForChangeAttributes;
    public $testUuid;
    public $test;

    /**
     * @throws \Exception
     */
    public function mount($testUuid = null)
    {
        if (!$testUuid) {
            throw new \Exception('No Test provided for the edit modal.');
        }
        $this->setProperties($testUuid);
        if(!Gate::allows('canViewTestDetails',[$this->test])){
            $this->forceClose()->closeModal();
        }
        parent::mount();
    }

    protected function setRequestPropertyDefaults(): void
    {
        [$testKind, $subject, $edLevel, $edYear, $period] = $this->getRequestPropertiesForTest($this->test);
        $this->request = [
            'name'                 => $this->test->name,
            'abbreviation'         => $this->test->abbreviation,
            'test_kind_id'         => $testKind,
            'subject_id'           => $subject,
            'education_level_id'   => $edLevel,
            'education_level_year' => $edYear,
            'period_id'            => $period,
            'shuffle'              => $this->test->shuffle,
            'introduction'         => $this->test->introduction,
        ];

        $this->testForChangeAttributes = [
            'subject_id'           => $this->test->subject_id,
            'education_level_id'   => $this->test->education_level_id,
            'education_level_year' => $this->test->education_level_year,
        ];
    }

    protected function performModalAction(): Test
    {
        if ($this->shouldPromptForDuplicateOrUpdateModal()) {
            $this->emit('openModal', 'teacher.test-update-or-duplicate-confirm-modal', ['request' => $this->request, 'testUuid' => $this->testUuid]);
            return $this->test;
        }

        $this->test->fill($this->request);
        $this->test->save();
        $this->closeModal();

        return $this->test;
    }

    protected function finishSubmitting(Test $test): void
    {
        $this->emit('testSettingsUpdated', $this->request);
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

    public static function modalMaxWidthClass(): string
    {
        return 'max-w-[600px]';
    }

    public function updatingRequestEducationLevelId($value, $name)
    {
        $this->request['education_level_year'] = 1;
    }

    protected function setProperties($testUuid)
    {
        $this->test = Test::whereUuid($testUuid)->firstOrFail();
        $this->testUuid = $testUuid;
    }
}