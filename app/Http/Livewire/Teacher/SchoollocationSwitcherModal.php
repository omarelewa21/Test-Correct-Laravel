<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use LivewireUI\Modal\ModalComponent;
use tcCore\SchoolLocation;

class SchoollocationSwitcherModal extends ModalComponent
{
    public function switchToSchoolLocation($uuid) {
        $schoolLocation = SchoolLocation::whereUuid($uuid)->first();
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }
        if (! $schoolLocation) {
            abort(403);
        }
        if (!auth()->user()->isAllowedToSwitchToSchoolLocation($schoolLocation)) {
            abort(403);
        }

        $user->schoolLocation()->associate($schoolLocation);
        $user->createTrialPeriodRecordIfRequired();
        $user->save();
        $this->dispatchBrowserEvent('notify', ['message' => __('general.Actieve schoollocatie aangepast')]);
        return $user->redirectToCakeWithTemporaryLogin();
//        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.teacher.schoollocation-switcher-modal')->with(
            ['schoolLocations' => auth()->user()->allowedSchoolLocations->pluck('name', 'uuid')]
        );
    }
}
