<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\EducationLevel;
use tcCore\FileManagement;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Period;
use tcCore\SchoolLocationEducationLevel;
use tcCore\Subject;
use tcCore\Test;

class TestCreateModalWithFile extends TestCreateModal
{
    public $fileManagement = null;

    public function mount(FileManagement $fileManagement = null)
    {
        if (!auth()->user()->isToetsenbakker() || $fileManagement->has('test')) {
            abort(403);
        }
        $this->fileManagement = $fileManagement;

        parent::mount();

        $this->request = array_merge($this->request, $this->getRequestDefaults($fileManagement));
    }

    protected function performAfterSaveActions(Test $test)
    {
        $this->fileManagement->test_id = $test->getKey();
        $this->fileManagement->save();
    }

    public function getAllowedSubjects()
    {
        return Subject::get(['id', 'name'])->keyBy('id');
    }

    public function getAllowedPeriods()
    {
        return Period::currentlyActive()
            ->forSchoolLocation($this->fileManagement->schoolLocation)
            ->get(['id', 'name', 'start_date', 'end_date'])->keyBy('id');

    }

    public function getAllowedEducationLevels()
    {
        return EducationLevel::whereIn(
            'id',
            SchoolLocationEducationLevel::select('school_location_id')
                ->where('school_location_id', $this->fileManagement->school_location_id)
        )
            ->get(['id', 'name', 'max_years', 'uuid'])->keyBy('id');
    }

    /**
     * @param FileManagement|null $fileManagement
     * @return array
     */
    private function getRequestDefaults(FileManagement $fileManagement): array
    {
        $period = PeriodRepository::getCurrentPeriodForSchoolLocation($fileManagement->schoolLocation);

        return [
            'name'                 => $fileManagement->test_name,
            'test_kind_id'         => $fileManagement->test_kind_id,
            'subject_id'           => $fileManagement->subject_id,
            'education_level_id'   => $fileManagement->education_level_id,
            'education_level_year' => $fileManagement->education_level_year,
            'period_id'            => $period->getKey(),
        ];
    }
}
