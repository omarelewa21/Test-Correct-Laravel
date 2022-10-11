<?php

namespace tcCore\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use LivewireUI\Modal\ModalComponent;
use tcCore\SchoolLocation;

class SchoolLocationDeleteModal extends ModalComponent
{
    public string $uuid;

    public function render()
    {
        return view('livewire.school-location-delete-modal');
    }

    public function mount($schoolLocationUuid)
    {
        $this->uuid = $schoolLocationUuid;
    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        $schoolLocation = SchoolLocation::whereUuid($this->uuid)->first();

        if (!$schoolLocation->canDelete(Auth::user())) {
            return false;
        }

        $schoolLocation->delete();

        $this->forceClose()->closeModal();

        $this->dispatchBrowserEvent('notify', ['message' => __('school_location.has_been_deleted')]);
        $this->emit('school_location_deleted');

        return true;
    }
}
