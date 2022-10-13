<?php

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

trait ExamCoordinator
{
    private function handleExamCoordinatorChange()
    {
        // Only schoolmanagers can make the change - Different place?
        if (!optional(Auth::user())->isA('School manager')) {
            return true;
        }

        $schoolManager = Auth::user();

        if ($this->isDirty('is_examcoordinator')) {
            if (!(!!$this->getAttribute('is_examcoordinator'))) {
                $this->setAttribute('is_examcoordinator_for', 'NONE');
            }
            // Doe iets met de waarde?
        }

        if ($this->isDirty('is_examcoordinator') || $this->isDirty('is_examcoordinator_for')) {
            $this->handleExamCoordinatorForScopeChange($schoolManager, $this->getAttribute('is_examcoordinator_for'));
        }
    }

    /**
     * @param User $schoolManager
     * @param $scope
     * @return void
     */
    private function handleExamCoordinatorForScopeChange(User $schoolManager, $scope): void
    {
        if ($scope === 'NONE') {
            $this->setAttribute('is_examcoordinator', 0);
            $this->removeSchoolLocationsExceptTheOneFromSchoolManager($schoolManager);
        }

        if ($scope === 'SCHOOL_LOCATION') {
            $this->removeSchoolLocationsExceptTheOneFromSchoolManager($schoolManager);
            $this->addSchoolLocation($schoolManager->schoolLocation);
        }

        if ($scope === 'SCHOOL') {
            $schoolLocations = $schoolManager->schoolLocation->school->schoolLocations;

            $schoolLocations->each(function ($location) {
                $this->addSchoolLocation($location);
            });
        }

        $this->setAttribute('session_hash', '');
    }

    /**
     * @param User $schoolManager
     * @return SchoolLocation[]
     */
    private function getSchoolLocationsToRemove(User $schoolManager)
    {
        return $this->allowedSchoolLocations->reject(fn($location) => $location->getKey() === $schoolManager->schoolLocation->getKey());
    }

    /**
     * @param User $schoolManager
     * @return void
     */
    private function removeSchoolLocationsExceptTheOneFromSchoolManager(User $schoolManager): void
    {
        $schoolManagerLocation = $schoolManager->schoolLocation;
        if ($this->hasMultipleSchoolLocations()) {
            $this->getSchoolLocationsToRemove($schoolManager)->each(function ($location) {
                $this->removeSchoolLocation($location);
            });
        }

        if ($this->schoolLocation->getKey() !== $schoolManagerLocation->getKey()) {
            $locationToRemove = $this->schoolLocation;
            $this->addSchoolLocation($schoolManagerLocation);
            $this->removeSchoolLocation($locationToRemove);
        }

        $this->setAttribute('school_location_id', $schoolManagerLocation->getKey());
    }

    public function isValidExamCoordinator($checkIfGlobal = true)
    {
        if (!$this->is_examcoordinator || is_null($this->is_examcoordinator_for)) {
            return false;
        }

        if ($checkIfGlobal) {
            // Check if exam coordinator has access to classes in school or school location
            return $this->is_examcoordinator_for !== 'NONE';
        }

        return true;
    }

    public function isSchoolExamCoordinator()
    {
        return $this->isValidExamCoordinator() && $this->is_examcoordinator_for === 'SCHOOL';
    }

    public function isSchoolLocationExamCoordinator()
    {
        return $this->isValidExamCoordinator() && $this->is_examcoordinator_for === 'SCHOOL_LOCATION';
    }
}