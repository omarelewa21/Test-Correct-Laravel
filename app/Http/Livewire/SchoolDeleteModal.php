<?php

namespace tcCore\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\Auth;
use tcCore\School;

class SchoolDeleteModal extends TCModalComponent
{
    public string $uuid;

    public function render()
    {
        return view('livewire.school-delete-modal');
    }

    public function mount($schoolUuid)
    {
        $this->uuid = $schoolUuid;
    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        $school = School::whereUuid($this->uuid)->first();

        if (!$school->canDelete(Auth::user())) {
            return false;
        }

        $school->delete();

        $this->forceClose()->closeModal();

        $this->dispatchBrowserEvent('notify', ['message' => __('school.has_been_deleted')]);
        $this->emit('school_deleted');

        return true;
    }
}
