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

        if ($this->isDirty('is_examcoordinator') || $this->wasRecentlyCreated) {
            if (!(!!$this->getAttribute('is_examcoordinator'))) {
                $this->setAttribute('is_examcoordinator_for', 'NONE');
            }
            // Doe iets met de waarde?
        }

        if ($this->isDirty('is_examcoordinator') || $this->isDirty('is_examcoordinator_for') || ($this->wasRecentlyCreated && $this->getAttribute('is_examcoordinator'))) {
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
            $this->refresh();
            $this->addSchoolLocation($schoolManager->schoolLocation);
        }

        if ($scope === 'SCHOOL_LOCATION') {
            $this->removeSchoolLocationsExceptTheOneFromSchoolManager($schoolManager);
            $this->refresh();
            $this->addSchoolLocation($schoolManager->schoolLocation);
        }

        if ($scope === 'SCHOOL') {
            $this->refresh();
            $schoolManager->schoolLocation->school->schoolLocations->each(function ($location) {
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

    public function isValidExamCoordinator()
    {
        return $this->is_examcoordinator && $this->is_examcoordinator_for !== null && $this->is_examcoordinator_for !== 'NONE';
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