<?php

namespace tcCore\Http\Livewire\Toetsenbakker;

use tcCore\FileManagement;
use tcCore\Http\Traits\Modal\ToetsenbakkerTestActions;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Test;

class TestCreateModal extends \tcCore\Http\Livewire\TestCreateModal
{
    use ToetsenbakkerTestActions;

    public $fileManagement;

    public function mount(FileManagement $fileManagement = null)
    {
        if (!auth()->user()->isToetsenbakker() || $fileManagement->test()->exists()) {
            abort(403);
        }
        $this->fileManagement = $fileManagement;

        parent::mount();

        $this->extendPropertyDefaults();
    }

    /**
     * @param FileManagement|null $fileManagement
     * @return array
     */
    private function extendPropertyDefaults(): void
    {
        $period = PeriodRepository::getCurrentPeriodForSchoolLocation($this->fileManagement->schoolLocation);

        $this->request = array_merge(
            $this->request,
            [
                'name'                 => $this->fileManagement->test_name,
                'test_kind_id'         => $this->fileManagement->test_kind_id,
                'subject_id'           => $this->fileManagement->subject_id,
                'education_level_id'   => $this->fileManagement->education_level_id,
                'education_level_year' => $this->fileManagement->education_level_year,
                'period_id'            => $period->getKey(),
            ]
        );
    }

    /* Method overrides */
    protected function createTestFromRequest(): Test
    {
        $test = parent::createTestFromRequest();

        $this->fileManagement->test_id = $test->getKey();
        $this->fileManagement->save();

        return $test;
    }

    public function render()
    {
        return view('livewire.teacher.test-create-modal');
    }
}
