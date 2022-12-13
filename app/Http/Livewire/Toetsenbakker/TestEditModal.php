<?php

namespace tcCore\Http\Livewire\Toetsenbakker;

use tcCore\EducationLevel;
use tcCore\Period;
use tcCore\SchoolLocationEducationLevel;
use tcCore\Subject;

class TestEditModal extends \tcCore\Http\Livewire\TestEditModal
{
    public $fileManagement;

    public function mount($testUuid = null)
    {
        parent::mount($testUuid);
    }
    public function getAllowedSubjects()
    {
        return Subject::where('id', $this->fileManagement->subject_id)->get(['id', 'name'])->keyBy('id');
    }

    public function getAllowedPeriods()
    {
        return Period::currentlyActive()
            ->forSchoolLocation($this->fileManagement->schoolLocation)
            ->get(['id', 'name', 'start_date', 'end_date'])
            ->keyBy('id');
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

    public function render()
    {
        return view('livewire.teacher.test-edit-modal');
    }
    protected function setProperties($testUuid)
    {
        parent::setProperties($testUuid);
        $this->fileManagement = $this->test->fileManagement;
    }
}