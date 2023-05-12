<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\SchoolLocation;

class SchoollocationSwitcherModal extends TCModalComponent
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
