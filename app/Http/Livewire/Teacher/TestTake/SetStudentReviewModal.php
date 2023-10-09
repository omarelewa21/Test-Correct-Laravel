<?php

namespace tcCore\Http\Livewire\Teacher\TestTake;

use tcCore\Http\Livewire\TCModalComponent;

class SetStudentReviewModal extends TCModalComponent
{
    public string $testTakeUuid;

    public $showResults;
    public $showCorrectionModel;

    protected array $rules = [
        'showResults'         => 'required',
        'showCorrectionModel' => 'bool'
    ];

    protected function validationAttributes()
    {
        return [
            'showResults' => strtolower(__('teacher.Datum'))
        ];
    }

    public function mount(\tcCore\TestTake $testTake): void
    {
        $this->testTakeUuid = $testTake->uuid;
        $this->showResults = $testTake->show_results;
        $this->showCorrectionModel = $testTake->show_correction_model;
    }

    public function render()
    {
        return view('livewire.teacher.test-take.set-student-review-modal');
    }

    public static function modalMaxWidthClass(): string
    {
        return 'max-w-[600px]';
    }

    public function continue(): void
    {
        $this->validate();
        $testTake = \tcCore\TestTake::whereUuid($this->testTakeUuid)->first();

        if ($testTake->show_results !== $this->showResults) {
            $this->emit('refresh');
        }

        $testTake->show_results = $this->showResults;
        $testTake->review_active = true;
        $testTake->show_correction_model = $this->showCorrectionModel;

        $testTake->save();

        $this->closeModal();
    }
}
