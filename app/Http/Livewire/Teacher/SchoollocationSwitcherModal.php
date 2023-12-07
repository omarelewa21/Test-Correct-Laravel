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
        $currentSchoolLocation = $user->schoolLocation;
        if (!$user) {
            abort(403);
        }
        if (! $schoolLocation) {
            abort(403);
        }
        if (!auth()->user()->isAllowedToSwitchToSchoolLocation($schoolLocation)) {
            abort(403);
        }

//        $user->schoolLocation()->associate($schoolLocation);
        $user->school_location_id = $schoolLocation->getKey();
        $user->save();
        $user->refresh();
        $user->createTrialPeriodRecordIfRequired();
        $user->save();
        if(!$currentSchoolLocation->block_local_login && $schoolLocation->block_local_login){
            // we need to move away towards entree;
            // but first we need to go to cake to logout there, then come back to laravel, logout and move to entree
//            auth()->logout();
//            $redirectUrl = route('saml2_login', ['entree']);
            $redirectUrl = config('app.url_roundtrip_entree');
            return redirect()->to($redirectUrl);

        }
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
